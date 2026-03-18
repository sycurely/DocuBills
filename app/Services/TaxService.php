<?php

namespace App\Services;

use App\Models\Tax;
use Illuminate\Support\Collection;

class TaxService
{
    /**
     * Get all taxes grouped by type.
     */
    public static function getAllGrouped(): array
    {
        $taxes = Tax::orderedByCalcOrder()->get();
        
        return [
            'line' => $taxes->where('tax_type', 'line')->values(),
            'invoice' => $taxes->where('tax_type', 'invoice')->values(),
        ];
    }

    /**
     * Get line-level taxes.
     */
    public static function getLineTaxes()
    {
        return Tax::lineLevel()->orderBy('name')->get();
    }

    /**
     * Get invoice-level taxes ordered by calculation order.
     */
    public static function getInvoiceTaxes()
    {
        return Tax::invoiceLevel()->orderedByCalcOrder()->get();
    }

    /**
     * Calculate tax amount.
     */
    public static function calculateTax(float $amount, float $percentage): float
    {
        return round($amount * ($percentage / 100), 2);
    }

    /**
     * Normalize tax type into supported values.
     */
    public static function normalizeTaxType(mixed $value): string
    {
        $type = strtolower(trim((string) $value));
        return $type === 'invoice' ? 'invoice' : 'line';
    }

    /**
     * Normalize calc order to deterministic range.
     * Line-level taxes always use calc order 1.
     */
    public static function normalizeCalcOrder(mixed $value, ?string $taxType = null): int
    {
        $normalizedType = self::normalizeTaxType($taxType ?? 'line');
        if ($normalizedType === 'line') {
            return 1;
        }

        if ($value === null || $value === '') {
            return 1;
        }

        $order = (int) $value;
        if ($order < 1) {
            return 1;
        }

        if ($order > 100) {
            return 100;
        }

        return $order;
    }

    /**
     * Keep only valid line-level tax id (or null).
     */
    public static function sanitizeLineTaxId(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $id = (int) $value;
        if ($id <= 0) {
            return null;
        }

        return Tax::lineLevel()->whereKey($id)->exists() ? $id : null;
    }

    /**
     * Keep only valid line-level tax ids ordered by name/id.
     *
     * @return array<int>
     */
    public static function sanitizeLineTaxIds(array $ids): array
    {
        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return [];
        }

        return Tax::lineLevel()
            ->whereIn('id', $ids)
            ->orderBy('name')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Keep only valid invoice-level tax ids ordered for calculation.
     *
     * @return array<int>
     */
    public static function sanitizeInvoiceTaxIds(array $ids): array
    {
        $ids = collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if (empty($ids)) {
            return [];
        }

        return Tax::invoiceLevel()
            ->whereIn('id', $ids)
            ->orderedByCalcOrder()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Load invoice-level taxes by ids in deterministic calculation order.
     */
    public static function getInvoiceTaxesForCalculation(array $ids): Collection
    {
        $sanitized = self::sanitizeInvoiceTaxIds($ids);
        if (empty($sanitized)) {
            return collect();
        }

        return Tax::invoiceLevel()
            ->whereIn('id', $sanitized)
            ->orderedByCalcOrder()
            ->get();
    }

    /**
     * Load line-level taxes by ids in deterministic order.
     */
    public static function getLineTaxesForCalculation(array $ids): Collection
    {
        $sanitized = self::sanitizeLineTaxIds($ids);
        if (empty($sanitized)) {
            return collect();
        }

        return Tax::lineLevel()
            ->whereIn('id', $sanitized)
            ->orderBy('name')
            ->orderBy('id')
            ->get();
    }
}
