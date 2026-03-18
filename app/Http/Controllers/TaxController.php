<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Tax;
use App\Services\TaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaxController extends Controller
{
    private function ensureSettingsAccess(): void
    {
        if (!has_any_setting_permission()) {
            abort(403, 'Access denied');
        }
    }

    /**
     * Show the tax management page.
     */
    public function index()
    {
        $this->ensureSettingsAccess();
        $taxes = Tax::orderBy('id')->get();
        return view('settings.taxes', compact('taxes'));
    }

    /**
     * Handle tax API operations (create, update, delete).
     */
    public function api(Request $request): JsonResponse
    {
        if (!has_any_setting_permission()) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }

        $action = $request->input('action');

        switch ($action) {
            case 'create':
                return $this->create($request);
            case 'update':
                return $this->update($request);
            case 'delete':
                return $this->delete($request);
            default:
                return response()->json(['success' => false, 'message' => 'Unknown action'], 400);
        }
    }

    /**
     * Create a new tax.
     */
    private function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'percentage' => 'required|numeric|min:0|max:100',
            'tax_type' => 'nullable|in:line,invoice',
            'calc_order' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        $tax = Tax::create([
            'name' => trim($request->input('name')),
            'percentage' => $request->input('percentage'),
            'tax_type' => TaxService::normalizeTaxType($request->input('tax_type', 'line')),
            'calc_order' => TaxService::normalizeCalcOrder(
                $request->input('calc_order', 1),
                $request->input('tax_type', 'line')
            ),
        ]);

        return response()->json([
            'success' => true,
            'id' => $tax->id,
            'name' => $tax->name,
            'percentage' => $tax->percentage,
            'tax_type' => $tax->tax_type,
            'calc_order' => $tax->calc_order,
        ]);
    }

    /**
     * Update an existing tax.
     */
    private function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:taxes,id',
            'name' => 'required|string|max:100',
            'percentage' => 'required|numeric|min:0|max:100',
            'tax_type' => 'nullable|in:line,invoice',
            'calc_order' => 'nullable|integer|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data',
                'errors' => $validator->errors()
            ], 422);
        }

        $tax = Tax::findOrFail($request->input('id'));
        $taxType = TaxService::normalizeTaxType($request->input('tax_type', 'line'));
        $tax->update([
            'name' => trim($request->input('name')),
            'percentage' => $request->input('percentage'),
            'tax_type' => $taxType,
            'calc_order' => TaxService::normalizeCalcOrder($request->input('calc_order', 1), $taxType),
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a tax.
     */
    private function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:taxes,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid ID'
            ], 422);
        }

        $tax = Tax::findOrFail($request->input('id'));
        $tax->delete();

        return response()->json(['success' => true]);
    }
}
