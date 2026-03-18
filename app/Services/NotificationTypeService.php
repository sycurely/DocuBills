<?php

namespace App\Services;

use App\Models\NotificationType;

class NotificationTypeService
{
    /**
     * Return all active notification types keyed by slug.
     */
    public static function allKeyed(): array
    {
        return NotificationType::query()
            ->whereNull('deleted_at')
            ->orderBy('label')
            ->get(['slug', 'label'])
            ->mapWithKeys(fn ($row) => [$row->slug => $row->label])
            ->toArray();
    }

    /**
     * Validate whether a slug exists in active notification types.
     */
    public static function isValidSlug(?string $slug): bool
    {
        if (empty($slug)) {
            return false;
        }

        return NotificationType::query()
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->exists();
    }
}
