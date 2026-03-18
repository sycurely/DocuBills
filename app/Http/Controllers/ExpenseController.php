<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request)
    {
        if (!has_permission('access_expenses_tab')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Expense::with(['client', 'creator']);

        // Apply ownership filter unless user has 'view_all_expenses' permission
        if (!has_permission('view_all_expenses')) {
            $query->where('created_by', Auth::id());
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vendor', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Show deleted or active
        if ($request->filled('show_deleted') && $request->show_deleted === '1') {
            $query->onlyTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        $expenses = $query->orderByDesc('expense_date')->paginate(20);

        // Get clients for filter dropdown
        $clientsQuery = Client::whereNull('deleted_at');
        if (!has_permission('view_all_clients')) {
            $clientsQuery->where('created_by', Auth::id());
        }
        $clients = $clientsQuery->orderBy('company_name')->get();

        // Get unique categories for filter
        $categories = Expense::whereNull('deleted_at')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        return view('expenses.index', compact('expenses', 'clients', 'categories'));
    }

    /**
     * Show the form for creating a new expense.
     */
    public function create()
    {
        if (!has_permission('add_expense')) {
            abort(403, 'Unauthorized action.');
        }

        $clientsQuery = Client::whereNull('deleted_at');
        if (!has_permission('view_all_clients')) {
            $clientsQuery->where('created_by', Auth::id());
        }
        $clients = $clientsQuery->orderBy('company_name')->get();

        return view('expenses.create', compact('clients'));
    }

    /**
     * Store a newly created expense.
     */
    public function store(Request $request)
    {
        if (!has_permission('add_expense')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'expense_date' => 'required|date',
            'vendor' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'is_recurring' => 'nullable|boolean',
            'status' => 'nullable|in:Paid,Unpaid',
            'payment_method' => 'nullable|string|max:255',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120', // 5MB max
            'payment_proof' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'email_cc' => 'nullable|string|max:255',
            'email_bcc' => 'nullable|string|max:255',
        ]);

        $data = $validated;
        $data['created_by'] = Auth::id();
        $data['status'] = $data['status'] ?? 'Unpaid';
        $data['is_recurring'] = $data['is_recurring'] ?? false;

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            $receipt = $request->file('receipt');
            $filename = time() . '_' . $receipt->getClientOriginalName();
            $path = $receipt->storeAs('expense_receipts', $filename, 'uploads');
            $data['receipt_url'] = '/storage/uploads/' . $path;
        }

        // Handle payment proof upload
        if ($request->hasFile('payment_proof')) {
            $proof = $request->file('payment_proof');
            $filename = 'proof_' . time() . '_' . $proof->getClientOriginalName();
            $path = $proof->storeAs('expense_receipts', $filename, 'uploads');
            $data['payment_proof'] = '/storage/uploads/' . $path;
        }

        Expense::create($data);

        return redirect()->route('expenses.index')->with('success', 'Expense added successfully.');
    }

    /**
     * Display the specified expense.
     */
    public function show(Expense $expense)
    {
        if (!has_permission('view_expenses') && !has_permission('view_all_expenses')) {
            abort(403, 'Unauthorized action.');
        }

        // Enforce ownership unless 'view_all_expenses' permission
        if (!has_permission('view_all_expenses') && $expense->created_by !== Auth::id()) {
            abort(404);
        }

        $expense->load(['client', 'creator']);

        return view('expenses.show', compact('expense'));
    }

    /**
     * Show the form for editing the specified expense.
     */
    public function edit(Expense $expense)
    {
        if (!has_permission('edit_expense')) {
            abort(403, 'Unauthorized action.');
        }

        // Enforce ownership unless 'view_all_expenses' permission
        if (!has_permission('view_all_expenses') && $expense->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $clientsQuery = Client::whereNull('deleted_at');
        if (!has_permission('view_all_clients')) {
            $clientsQuery->where('created_by', Auth::id());
        }
        $clients = $clientsQuery->orderBy('company_name')->get();

        return view('expenses.edit', compact('expense', 'clients'));
    }

    /**
     * Update the specified expense.
     */
    public function update(Request $request, Expense $expense)
    {
        if (!has_permission('edit_expense')) {
            abort(403, 'Unauthorized action.');
        }

        // Enforce ownership unless 'view_all_expenses' permission
        if (!has_permission('view_all_expenses') && $expense->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'expense_date' => 'required|date',
            'vendor' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'client_id' => 'nullable|exists:clients,id',
            'is_recurring' => 'nullable|boolean',
            'status' => 'nullable|in:Paid,Unpaid',
            'payment_method' => 'nullable|string|max:255',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'payment_proof' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'email_cc' => 'nullable|string|max:255',
            'email_bcc' => 'nullable|string|max:255',
        ]);

        $data = $validated;

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            // Delete old receipt if exists
            if ($expense->receipt_url) {
                $oldPath = str_replace('/storage/uploads/', '', $expense->receipt_url);
                Storage::disk('uploads')->delete($oldPath);
            }

            $receipt = $request->file('receipt');
            $filename = $expense->id . '_' . time() . '_' . $receipt->getClientOriginalName();
            $path = $receipt->storeAs('expense_receipts', $filename, 'uploads');
            $data['receipt_url'] = '/storage/uploads/' . $path;
        }

        // Handle payment proof upload
        if ($request->hasFile('payment_proof')) {
            // Delete old proof if exists
            if ($expense->payment_proof) {
                $oldPath = str_replace('/storage/uploads/', '', $expense->payment_proof);
                Storage::disk('uploads')->delete($oldPath);
            }

            $proof = $request->file('payment_proof');
            $filename = 'proof_' . $expense->id . '_' . time() . '_' . $proof->getClientOriginalName();
            $path = $proof->storeAs('expense_receipts', $filename, 'uploads');
            $data['payment_proof'] = '/storage/uploads/' . $path;
        }

        $expense->update($data);

        return redirect()->route('expenses.index')->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified expense (soft delete).
     */
    public function destroy(Expense $expense)
    {
        if (!has_permission('delete_expense')) {
            abort(403, 'Unauthorized action.');
        }

        // Enforce ownership unless 'view_all_expenses' permission
        if (!has_permission('view_all_expenses') && $expense->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $expense->delete();

        return redirect()->route('expenses.index')->with('success', 'Expense deleted successfully.');
    }

    /**
     * Restore the specified soft-deleted expense.
     */
    public function restore(string $id)
    {
        if (!has_permission('undo_recent_expense') && !has_permission('undo_all_expenses')) {
            abort(403, 'Unauthorized action.');
        }

        $expense = Expense::onlyTrashed()->findOrFail($id);

        // Enforce ownership unless 'view_all_expenses' permission
        if (!has_permission('view_all_expenses') && $expense->created_by !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $expense->restore();

        return redirect()->route('expenses.index')->with('success', 'Expense restored successfully.');
    }

    /**
     * Restore all soft-deleted expenses.
     */
    public function restoreAll()
    {
        if (!has_permission('undo_all_expenses')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Expense::onlyTrashed();

        // Apply ownership filter unless user has 'view_all_expenses' permission
        if (!has_permission('view_all_expenses')) {
            $query->where('created_by', Auth::id());
        }

        $query->restore();

        return redirect()->route('expenses.index')->with('success', 'All deleted expenses have been restored!');
    }

    /**
     * Undo the most recent expense deletion.
     */
    public function undoRecent()
    {
        if (!has_permission('undo_recent_expense')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Expense::onlyTrashed()->orderByDesc('deleted_at');

        // Apply ownership filter unless user has 'view_all_expenses' permission
        if (!has_permission('view_all_expenses')) {
            $query->where('created_by', Auth::id());
        }

        $expense = $query->first();

        if ($expense) {
            $expense->restore();
            return redirect()->route('expenses.index')->with('success', 'Last deletion undone!');
        }

        return redirect()->route('expenses.index')->with('error', 'No deleted expenses found.');
    }

    /**
     * Change expense payment status.
     */
    public function changeStatus(Request $request, Expense $expense)
    {
        if (!has_permission('change_expense_status')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'status' => 'required|in:Paid,Unpaid',
        ]);

        $expense->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Expense status updated.');
    }

    /**
     * Export expenses to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        if (!has_permission('export_expenses')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Expense::with(['client', 'creator']);

        // Apply ownership filter unless user has 'view_all_expenses' permission
        if (!has_permission('view_all_expenses')) {
            $query->where('created_by', Auth::id());
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        $expenses = $query->whereNull('deleted_at')->orderByDesc('expense_date')->get();

        $filename = 'expenses_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($expenses) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Date',
                'Vendor',
                'Amount',
                'Category',
                'Client',
                'Status',
                'Payment Method',
                'Recurring',
                'Notes',
                'Created By',
            ]);

            // CSV rows
            foreach ($expenses as $expense) {
                fputcsv($file, [
                    $expense->expense_date->format('Y-m-d'),
                    $expense->vendor,
                    $expense->amount,
                    $expense->category ?? '',
                    $expense->client->company_name ?? '',
                    $expense->status,
                    $expense->payment_method ?? '',
                    $expense->is_recurring ? 'Yes' : 'No',
                    $expense->notes ?? '',
                    $expense->creator->username ?? '',
                ]);
            }

            fclose($file);
        }, $filename, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
