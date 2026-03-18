<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Expense;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        if (!has_permission('view_dashboard')) {
            abort(403, 'Unauthorized action.');
        }

        return view('dashboard.index');
    }

    /**
     * Get dashboard data (API endpoint).
     */
    public function getData(Request $request): JsonResponse
    {
        if (!has_permission('view_dashboard')) {
            abort(403, 'Unauthorized action.');
        }

        $period = $request->input('period', 'daily');
        $paidClients = $request->boolean('paid_clients');
        $unpaidClients = $request->boolean('unpaid_clients');

        // Handle specific client requests
        if ($unpaidClients) {
            $topUnpaid = Invoice::where('status', 'Unpaid')
                ->whereNull('deleted_at')
                ->select('bill_to_name', DB::raw('COUNT(*) as count'))
                ->groupBy('bill_to_name')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            return response()->json(['top_unpaid' => $topUnpaid]);
        }

        if ($paidClients) {
            $topClients = Invoice::whereNull('deleted_at')
                ->select('bill_to_name', DB::raw('COUNT(*) as total'))
                ->groupBy('bill_to_name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            return response()->json(['top_clients' => $topClients]);
        }

        $response = [
            'status' => ['paid' => 0, 'unpaid' => 0],
            'labels' => [],
            'paid_series' => [],
            'unpaid_series' => [],
            'total_revenue' => 0,
            'top_clients' => [],
            'recent_invoices' => [],
        ];

        try {
            // Count Paid/Unpaid (for doughnut chart)
            $statusCounts = Invoice::whereNull('deleted_at')
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->get();

            foreach ($statusCounts as $row) {
                $status = strtolower($row->status);
                if (isset($response['status'][$status])) {
                    $response['status'][$status] = (int) $row->count;
                }
            }

            // Time-based grouped bar data
            $query = Invoice::whereNull('deleted_at');

            switch ($period) {
                case 'monthly':
                    $data = $query->select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') AS label"),
                        DB::raw("SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid"),
                        DB::raw("SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid")
                    )
                        ->where('created_at', '>=', now()->subMonths(6))
                        ->groupBy('label')
                        ->orderBy('label')
                        ->get();
                    break;

                case 'yearly':
                    $data = $query->select(
                        DB::raw('YEAR(created_at) AS label'),
                        DB::raw("SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid"),
                        DB::raw("SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid")
                    )
                        ->groupBy('label')
                        ->orderByDesc('label')
                        ->limit(5)
                        ->get();
                    break;

                case 'all':
                    $data = $query->select(
                        DB::raw("DATE_FORMAT(created_at, '%Y-%m') AS label"),
                        DB::raw("SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid"),
                        DB::raw("SUM(CASE WHEN status = 'Unpaid' THEN 1 ELSE 0 END) AS unpaid")
                    )
                        ->groupBy('label')
                        ->orderBy('label')
                        ->get();
                    break;

                case 'daily':
                default:
                    $data = $query->select(
                        DB::raw('DATE(created_at) AS label'),
                        DB::raw("COUNT(CASE WHEN status = 'Paid' THEN 1 END) AS paid"),
                        DB::raw("COUNT(CASE WHEN status = 'Unpaid' THEN 1 END) AS unpaid")
                    )
                        ->where('created_at', '>=', now()->subDays(7))
                        ->groupBy(DB::raw('DATE(created_at)'))
                        ->orderBy('label')
                        ->get();
                    break;
            }

            foreach ($data as $row) {
                $response['labels'][] = (string) $row->label;
                $response['paid_series'][] = (int) $row->paid;
                $response['unpaid_series'][] = (int) $row->unpaid;
            }

            // Total revenue (paid invoices)
            $response['total_revenue'] = (float) Invoice::where('status', 'Paid')
                ->whereNull('deleted_at')
                ->sum('total_amount');

            // Top 5 clients
            $topClients = Invoice::whereNull('deleted_at')
                ->select('bill_to_name', DB::raw('COUNT(*) AS total'))
                ->groupBy('bill_to_name')
                ->orderByDesc('total')
                ->limit(5)
                ->get();

            $response['top_clients'] = $topClients->map(function ($item) {
                return [
                    'bill_to_name' => $item->bill_to_name,
                    'total' => (int) $item->total,
                ];
            })->toArray();

            // Recent 5 invoices
            $recentInvoices = Invoice::whereNull('deleted_at')
                ->select('invoice_number', 'bill_to_name', 'total_amount', 'status', 'created_at')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $response['recent_invoices'] = $recentInvoices->map(function ($invoice) {
                return [
                    'invoice_number' => $invoice->invoice_number,
                    'bill_to_name' => $invoice->bill_to_name,
                    'total_amount' => (float) $invoice->total_amount,
                    'status' => $invoice->status,
                    'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                ];
            })->toArray();

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get dashboard summary (API endpoint).
     */
    public function getSummary(): JsonResponse
    {
        if (!has_permission('view_dashboard')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            // Total Revenue from Paid Invoices
            $totalRevenue = (float) Invoice::where('status', 'Paid')
                ->whereNull('deleted_at')
                ->sum('total_amount');

            // Total Deficit from Unpaid Invoices
            $totalDeficit = (float) Invoice::where('status', 'Unpaid')
                ->whereNull('deleted_at')
                ->sum('total_amount');

            // Recent 5 Invoices
            $recentInvoices = Invoice::whereNull('deleted_at')
                ->select('invoice_number', 'bill_to_name', 'total_amount', 'status', 'created_at')
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();

            $response = [
                'total_revenue' => $totalRevenue,
                'total_deficit' => $totalDeficit,
                'recent_invoices' => $recentInvoices->map(function ($invoice) {
                    return [
                        'invoice_number' => $invoice->invoice_number,
                        'bill_to_name' => $invoice->bill_to_name,
                        'total_amount' => (float) $invoice->total_amount,
                        'status' => $invoice->status,
                        'created_at' => $invoice->created_at->format('Y-m-d'),
                    ];
                })->toArray(),
            ];

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to load dashboard summary',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
