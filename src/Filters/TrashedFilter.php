<?php

namespace InertiaResource\Filters;

/**
 * TrashedFilter - For filtering soft-deleted records
 *
 * Usage:
 * TrashedFilter::make()
 *
 * Automatically provides options:
 * - '' => 'Without Trashed' (default)
 * - 'with' => 'With Trashed'
 * - 'only' => 'Only Trashed'
 *
 * Query logic is automatically applied using withTrashed() and onlyTrashed()
 */
class TrashedFilter extends SelectFilter
{
    public static function make(?string $name = 'trashed', ?string $label = 'Deleted Records'): self
    {
        $instance = new self;
        $instance->name = $name ?? 'trashed';
        $instance->label = $label ?? 'Deleted Records';
        $instance->options = [
            '' => 'Without Trashed',
            'with' => 'With Trashed',
            'only' => 'Only Trashed',
        ];

        // Set default query callback
        $instance->query(function ($query, $value) {
            if ($value === 'with') {
                return $query->withTrashed();
            } elseif ($value === 'only') {
                return $query->onlyTrashed();
            }

            return $query;
        });

        return $instance;
    }
}

