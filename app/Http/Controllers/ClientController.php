<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $canViewAll = $user->isAdminOrSuperAdmin() || has_permission('view_all_clients');

        $query = Client::query();

        // Apply ownership filter unless user can view all
        if (!$canViewAll) {
            $query->where('created_by', $user->id);
        }

        // Handle search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('company_name', 'like', $search . '%')
                  ->orWhere('email', 'like', $search . '%')
                  ->orWhere('phone', 'like', $search . '%');
            });
        }

        // Handle filter (active/deleted)
        if ($request->has('filter')) {
            if ($request->filter === 'deleted') {
                $query->onlyTrashed();
            }
        } else {
            $query->whereNull('deleted_at');
        }

        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100], true) ? $perPage : 10;

        $query->with('creator:id,full_name,username')
            ->withCount('invoices as total_invoices')
            ->withCount(['invoices as paid_invoices' => fn ($q) => $q->where('status', 'Paid')])
            ->withCount(['invoices as unpaid_invoices' => fn ($q) => $q->where('status', 'Unpaid')])
            ->withMax('invoices', 'invoice_date');

        $clients = $query->orderBy('company_name')->paginate($perPage)->withQueryString();

        $canAddClient = can_add_client();
        $canEditClient = can_edit_client();

        // Debug: why New Client / Edit Client buttons show or hide
        $user->loadMissing('role');
        Log::channel('single')->debug('Clients index permission check', [
            'user_id' => $user->id,
            'username' => $user->username ?? null,
            'role_id' => $user->role_id,
            'role_name' => $user->role?->name ?? null,
            'isAdminOrSuperAdmin' => $user->isAdminOrSuperAdmin(),
            'canAddClient' => $canAddClient,
            'canEditClient' => $canEditClient,
            'canViewAll' => $canViewAll,
        ]);

        return view('clients.index', compact('clients', 'canViewAll', 'canAddClient', 'canEditClient'))->with('activeMenu', 'clients');
    }

    /**
     * Display the client detail page (view).
     */
    public function showPage(Client $client)
    {
        $user = Auth::user();
        $canViewAll = $user->isAdminOrSuperAdmin() || has_permission('view_all_clients');

        if (!$canViewAll && $client->created_by !== $user->id) {
            abort(403, 'Access denied');
        }

        $client->load('creator:id,full_name,username,email');
        $client->loadCount('invoices');
        $client->loadCount(['invoices as paid_invoices' => fn ($q) => $q->where('status', 'Paid')]);
        $client->loadCount(['invoices as unpaid_invoices' => fn ($q) => $q->where('status', 'Unpaid')]);

        $canEditClient = can_edit_client();

        return view('clients.show', compact('client', 'canEditClient'))->with('activeMenu', 'clients');
    }

    /**
     * Store a newly created client.
     */
    public function store(StoreClientRequest $request)
    {
        $client = Client::create([
            'company_name' => $request->company_name,
            'representative' => $request->representative,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'gst_hst' => $request->gst_hst,
            'notes' => $request->notes,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('clients.index')->with('success', 'Client added successfully!');
    }

    /**
     * Display the specified client.
     */
    public function show(Client $client)
    {
        $user = Auth::user();
        $canViewAll = $user->isAdminOrSuperAdmin() || has_permission('view_all_clients');

        // Check ownership unless can view all
        if (!$canViewAll && $client->created_by !== $user->id) {
            abort(403, 'Access denied');
        }

        return response()->json($client);
    }

    /**
     * Update the specified client.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $user = Auth::user();
        $canViewAll = $user->isAdminOrSuperAdmin() || has_permission('view_all_clients');

        // Check ownership unless can view all
        if (!$canViewAll && $client->created_by !== $user->id) {
            abort(403, 'Access denied');
        }

        $client->update([
            'company_name' => $request->company_name,
            'representative' => $request->representative,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'gst_hst' => $request->gst_hst,
            'notes' => $request->notes,
        ]);

        return redirect()->route('clients.index')->with('success', 'Client updated successfully!');
    }

    /**
     * Remove the specified client (soft delete).
     */
    public function destroy(Client $client)
    {
        if (!has_permission('delete_client')) {
            abort(403, 'Access denied');
        }

        $user = Auth::user();
        $canViewAll = has_permission('view_all_clients');

        // Check ownership unless can view all
        if (!$canViewAll && $client->created_by !== $user->id) {
            abort(403, 'Access denied');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully!');
    }

    /**
     * Restore a soft-deleted client.
     */
    public function restore($id)
    {
        if (!has_permission('restore_clients')) {
            abort(403, 'Access denied');
        }

        $client = Client::withTrashed()->findOrFail($id);
        $client->restore();

        return redirect()->route('clients.index')->with('success', 'Client restored successfully!');
    }

    /**
     * Restore all deleted clients.
     */
    public function restoreAll()
    {
        if (!has_permission('restore_clients') && !has_permission('undo_all_clients')) {
            abort(403, 'Access denied');
        }

        Client::onlyTrashed()->restore();

        return redirect()->route('clients.index')->with('success', 'All deleted clients have been restored!');
    }

    /**
     * Restore the most recently deleted client.
     */
    public function undoRecent()
    {
        if (!has_permission('restore_clients') && !has_permission('undo_recent_client')) {
            abort(403, 'Access denied');
        }

        $client = Client::onlyTrashed()->orderByDesc('deleted_at')->first();
        if (!$client) {
            return redirect()->route('clients.index')->with('error', 'No recently deleted client to restore.');
        }

        $client->restore();

        return redirect()->route('clients.index')->with('success', 'Most recent deletion has been undone!');
    }

    /**
     * Soft-delete all active clients (matches reference: "Delete All").
     */
    public function deleteAll()
    {
        if (!has_permission('delete_client')) {
            abort(403, 'Access denied');
        }

        $user = Auth::user();
        $canViewAll = has_permission('view_all_clients');

        $query = Client::query()->whereNull('deleted_at');
        if (!$canViewAll) {
            $query->where('created_by', $user->id);
        }
        $count = $query->delete(); // soft delete

        return redirect()->route('clients.index')->with('success', 'All clients have been deleted (soft delete). You can restore them using Undo buttons.');
    }

    /**
     * Export clients to CSV (Excel-compatible).
     */
    public function export(Request $request): StreamedResponse|Response
    {
        if (!has_permission('export_clients')) {
            abort(403, 'Access denied');
        }

        $user = Auth::user();
        $canViewAll = has_permission('view_all_clients');

        $query = Client::query()->whereNull('deleted_at');
        if (!$canViewAll) {
            $query->where('created_by', $user->id);
        }
        $clients = $query->orderBy('company_name')->get();

        $filename = 'clients_export_' . date('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($clients) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#', 'Company', 'Representative', 'Phone', 'Email', 'Address', 'GST/HST', 'Notes']);
            foreach ($clients as $i => $client) {
                fputcsv($out, [
                    $i + 1,
                    $client->company_name,
                    $client->representative ?? '',
                    $client->phone ?? '',
                    $client->email ?? '',
                    $client->address ?? '',
                    $client->gst_hst ?? '',
                    $client->notes ?? '',
                ]);
            }
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Search clients (AJAX endpoint).
     */
    public function search(Request $request): JsonResponse
    {
        if (!has_permission('create_invoice')) {
            return response()->json([]);
        }

        $q = trim($request->get('q', ''));
        if (empty($q) || strlen($q) > 80) {
            return response()->json([]);
        }

        $user = Auth::user();
        $canViewAll = has_permission('view_all_clients');

        $query = Client::whereNull('deleted_at')
            ->where('company_name', 'like', $q . '%');

        // Apply ownership filter unless user can view all
        if (!$canViewAll) {
            $query->where('created_by', $user->id);
        }

        $clients = $query->select('id', 'company_name', 'representative', 'phone', 'email', 'address')
            ->orderBy('company_name')
            ->limit(25)
            ->get();

        return response()->json($clients);
    }
}
