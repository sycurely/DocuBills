<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\EmailTemplate;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class TrashBinController extends Controller
{
    /**
     * Display deleted items grouped by resource type.
     */
    public function index()
    {
        if (!has_permission('access_trashbin')) {
            abort(403, 'Unauthorized action.');
        }

        $canViewAllTrash = has_permission('view_all_trash');

        $resources = [
            'invoices' => [
                'label' => 'Invoices',
                'rows' => $this->deletedInvoices($canViewAllTrash),
                'restore_route_type' => 'invoice',
                'force_route_type' => 'invoice',
            ],
            'clients' => [
                'label' => 'Clients',
                'rows' => $this->deletedClients($canViewAllTrash),
                'restore_route_type' => 'client',
                'force_route_type' => 'client',
            ],
            'expenses' => [
                'label' => 'Expenses',
                'rows' => $this->deletedExpenses($canViewAllTrash),
                'restore_route_type' => 'expense',
                'force_route_type' => 'expense',
            ],
            'email_templates' => [
                'label' => 'Email Templates',
                'rows' => $this->deletedEmailTemplates($canViewAllTrash),
                'restore_route_type' => 'email-template',
                'force_route_type' => 'email-template',
            ],
            'users' => [
                'label' => 'Users',
                'rows' => $this->deletedUsers($canViewAllTrash),
                'restore_route_type' => 'user',
                'force_route_type' => 'user',
            ],
        ];

        $counts = [];
        $total = 0;
        foreach ($resources as $key => $resource) {
            $count = $resource['rows']->count();
            $counts[$key] = $count;
            $total += $count;
        }

        return view('trash-bin.index', [
            'resources' => $resources,
            'counts' => $counts,
            'totalDeleted' => $total,
            'canRestoreDeletedItems' => has_permission('restore_deleted_items'),
            'canViewAllTrash' => $canViewAllTrash,
        ]);
    }

    /**
     * Restore a soft deleted record.
     */
    public function restore(string $type, int $id): RedirectResponse
    {
        if (!has_permission('restore_deleted_items')) {
            abort(403, 'Unauthorized action.');
        }

        [$modelClass] = $this->resolveResourceConfig($type);
        $item = $modelClass::onlyTrashed()->findOrFail($id);

        if (!$this->canAccessItem($item)) {
            abort(403, 'Unauthorized action.');
        }

        $item->restore();

        return redirect()->route('trash-bin.index')->with('success', 'Item restored successfully.');
    }

    /**
     * Permanently delete a soft deleted record.
     */
    public function forceDelete(string $type, int $id): RedirectResponse
    {
        [$modelClass, $requiredPermission] = $this->resolveResourceConfig($type);

        if (!has_permission($requiredPermission)) {
            abort(403, 'Unauthorized action.');
        }

        $item = $modelClass::onlyTrashed()->findOrFail($id);

        if (!$this->canAccessItem($item)) {
            abort(403, 'Unauthorized action.');
        }

        $item->forceDelete();

        return redirect()->route('trash-bin.index')->with('success', 'Item permanently deleted.');
    }

    private function resolveResourceConfig(string $type): array
    {
        $map = [
            'invoice' => [Invoice::class, 'delete_forever'],
            'client' => [Client::class, 'delete_client'],
            'expense' => [Expense::class, 'delete_expense_forever'],
            'email-template' => [EmailTemplate::class, 'delete_email_template'],
            'user' => [User::class, 'delete_user'],
        ];

        if (!isset($map[$type])) {
            abort(404);
        }

        return $map[$type];
    }

    private function canAccessItem(object $item): bool
    {
        if (has_permission('view_all_trash')) {
            return true;
        }

        if (isset($item->created_by)) {
            return (int) ($item->created_by ?? 0) === (int) Auth::id();
        }

        if ($item instanceof User) {
            return (int) $item->id === (int) Auth::id();
        }

        return false;
    }

    private function deletedInvoices(bool $canViewAllTrash)
    {
        $query = Invoice::onlyTrashed()
            ->with(['creator' => function ($query) {
                $query->withTrashed();
            }])
            ->orderByDesc('deleted_at');

        if (!$canViewAllTrash) {
            $query->where('created_by', Auth::id());
        }

        return $query->limit(100)->get();
    }

    private function deletedClients(bool $canViewAllTrash)
    {
        $query = Client::onlyTrashed()
            ->with(['creator' => function ($query) {
                $query->withTrashed();
            }])
            ->orderByDesc('deleted_at');

        if (!$canViewAllTrash) {
            $query->where('created_by', Auth::id());
        }

        return $query->limit(100)->get();
    }

    private function deletedExpenses(bool $canViewAllTrash)
    {
        $query = Expense::onlyTrashed()
            ->with(['creator' => function ($query) {
                $query->withTrashed();
            }])
            ->orderByDesc('deleted_at');

        if (!$canViewAllTrash) {
            $query->where('created_by', Auth::id());
        }

        return $query->limit(100)->get();
    }

    private function deletedEmailTemplates(bool $canViewAllTrash)
    {
        $query = EmailTemplate::onlyTrashed()
            ->with(['creator' => function ($query) {
                $query->withTrashed();
            }])
            ->orderByDesc('deleted_at');

        if (!$canViewAllTrash) {
            $query->where('created_by', Auth::id());
        }

        return $query->limit(100)->get();
    }

    private function deletedUsers(bool $canViewAllTrash)
    {
        $query = User::onlyTrashed()
            ->withTrashed()
            ->orderByDesc('deleted_at');

        if (!$canViewAllTrash) {
            $query->where('id', Auth::id());
        }

        return $query->limit(100)->get();
    }
}
