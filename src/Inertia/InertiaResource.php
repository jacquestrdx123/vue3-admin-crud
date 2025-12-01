<?php

namespace InertiaResource\Inertia;

use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use InertiaResource\Contracts\ColumnPreferenceRepository;

/**
 * Base Inertia Resource
 *
 * This class provides the foundation for defining Inertia-based resources with tables, forms, and actions.
 *
 * CRITICAL: Relationship Column Keys Must Use snake_case
 * -------------------------------------------------------
 * When defining columns in the table() method that access model relationships, you MUST use snake_case
 * for the relationship name in the column key, even if the relationship method is camelCase in the model.
 *
 * Why? Laravel serializes model relationships to snake_case when converting to arrays for JSON/Inertia.
 *
 * Example Resource Definition:
 * ```php
 * public static function table(): array
 * {
 *     return [
 *         'columns' => [
 *             // Model has: public function mieQuery() { ... }
 *             TextColumn::make('mie_query.request_id', 'Request ID'), // ✅ Correct
 *             // TextColumn::make('mieQuery.request_id', 'Request ID'), // ❌ Wrong - won't work!
 *
 *             // Model has: public function advancedPayment() { ... }
 *             MoneyColumn::make('advanced_payment.amount', 'Amount'), // ✅ Correct
 *             // MoneyColumn::make('advancedPayment.amount', 'Amount'), // ❌ Wrong - won't work!
 *         ],
 *     ];
 * }
 * ```
 *
 * Remember to Eager Load Relationships:
 * -------------------------------------
 * Override the getQuery() method in your controller to eager load relationships:
 *
 * ```php
 * protected function getQuery(): Builder
 * {
 *     return YourModel::query()->with(['relationshipOne', 'relationshipTwo']);
 * }
 * ```
 *
 * Common Mistakes:
 * - Using camelCase: 'proformaInvoice.total' ❌
 * - Correct snake_case: 'proforma_invoice.total' ✅
 * - Not eager loading the relationship in the controller ❌
 */
abstract class InertiaResource
{
    protected static ?string $model = null;

    protected static ?string $title = null;

    protected static ?string $slug = null;

    protected static ?string $navigationGroup = null;

    protected static ?string $navigationIcon = null;

    protected static ?int $navigationSort = null;

    protected static string $indexPage = 'Resources/Index';

    protected static string $createPage = 'Resources/Create';

    protected static string $editPage = 'Resources/Edit';

    protected static string $showPage = 'Resources/Show';

    abstract public static function table(): array;

    abstract public static function form(): array;

    public static function getModel(): string
    {
        return static::$model;
    }

    public static function getColumns(?Authenticatable $user = null): array
    {
        $tableConfig = static::table();

        $columns = array_map(function ($column) {
            return is_object($column) && method_exists($column, 'toArray') ? $column->toArray() : $column;
        }, $tableConfig['columns'] ?? []);

        // Apply user preferences if user is provided and repository is bound
        if ($user && App::bound(ColumnPreferenceRepository::class)) {
            $resourceSlug = static::getSlug();
            if ($resourceSlug) {
                /** @var ColumnPreferenceRepository $repository */
                $repository = App::make(ColumnPreferenceRepository::class);
                $preferences = $repository->getPreferencesForResource($user, $resourceSlug);
                if ($preferences) {
                    $columns = static::applyUserColumnPreferences($columns, $preferences);
                }
            }
        }

        return $columns;
    }

    protected static function applyUserColumnPreferences(array $columns, array $preferences): array
    {
        // Create a map of columns by key for quick lookup
        $columnMap = [];
        foreach ($columns as $column) {
            $key = is_array($column) ? ($column['key'] ?? null) : null;
            if ($key) {
                $columnMap[$key] = $column;
            }
        }

        $orderedColumns = [];
        $hiddenKeys = $preferences['hidden'] ?? [];

        // Apply order preference if provided
        if (! empty($preferences['order'])) {
            // Add columns in the specified order
            foreach ($preferences['order'] as $key) {
                if (isset($columnMap[$key])) {
                    $orderedColumns[] = $columnMap[$key];
                    unset($columnMap[$key]);
                }
            }
        }

        // Add any remaining columns that weren't in the order preference (new columns)
        foreach ($columnMap as $column) {
            $key = is_array($column) ? ($column['key'] ?? null) : null;
            if ($key && ! in_array($key, $preferences['order'] ?? [], true)) {
                $orderedColumns[] = $column;
            }
        }

        // If no order preference, use default order
        if (empty($preferences['order'])) {
            $orderedColumns = $columns;
        }

        // Filter out hidden columns
        $visibleColumns = [];
        foreach ($orderedColumns as $column) {
            $key = is_array($column) ? ($column['key'] ?? null) : null;
            if ($key && ! in_array($key, $hiddenKeys, true)) {
                $visibleColumns[] = $column;
            } elseif (! $key) {
                // Keep columns without keys (shouldn't happen, but be safe)
                $visibleColumns[] = $column;
            }
        }

        return $visibleColumns;
    }

    public static function getActions(): array
    {
        $tableConfig = static::table();

        return array_map(function ($action) {
            return is_object($action) && method_exists($action, 'toArray') ? $action->toArray() : $action;
        }, $tableConfig['actions'] ?? []);
    }

    public static function getBulkActions(): array
    {
        $tableConfig = static::table();

        return array_map(function ($action) {
            return is_object($action) && method_exists($action, 'toArray') ? $action->toArray() : $action;
        }, $tableConfig['bulkActions'] ?? []);
    }

    public static function getFilters(): array
    {
        $tableConfig = static::table();

        return array_map(function ($filter) {
            return is_object($filter) && method_exists($filter, 'toArray') ? $filter->toArray() : $filter;
        }, $tableConfig['filters'] ?? []);
    }

    /**
     * Get raw filter objects (for accessing callbacks)
     */
    public static function getFilterObjects(): array
    {
        $tableConfig = static::table();

        return $tableConfig['filters'] ?? [];
    }

    public static function getCustomFilters(): array
    {
        $tableConfig = static::table();

        return array_map(function ($filter) {
            return is_object($filter) && method_exists($filter, 'toArray') ? $filter->toArray() : $filter;
        }, $tableConfig['custom_filters'] ?? []);
    }

    /**
     * Get preset view metadata for client consumption, grouped by group key.
     */
    public static function getPresetViews(): array
    {
        $definitions = static::getPresetViewDefinitions();
        $grouped = [];
        $ungrouped = [];

        foreach ($definitions as $key => $definition) {
            $resolvedKey = is_array($definition) && isset($definition['key'])
                ? $definition['key']
                : $key;

            if (! is_string($resolvedKey) || $resolvedKey === '') {
                continue;
            }

            $label = is_array($definition) && isset($definition['label'])
                ? $definition['label']
                : Str::title(str_replace(['_', '-'], ' ', $resolvedKey));

            $preset = [
                'key' => $resolvedKey,
                'label' => $label,
            ];

            if (is_array($definition)) {
                foreach (['description', 'icon', 'color', 'badge'] as $metaKey) {
                    if (array_key_exists($metaKey, $definition)) {
                        $preset[$metaKey] = $definition[$metaKey];
                    }
                }

                $group = $definition['group'] ?? null;
                if ($group && is_string($group) && $group !== '') {
                    if (! isset($grouped[$group])) {
                        $groupLabel = $definition['group_label'] ?? Str::title(str_replace(['_', '-'], ' ', $group));
                        $grouped[$group] = [
                            'name' => $group,
                            'label' => $groupLabel,
                            'presets' => [],
                        ];
                    }
                    $grouped[$group]['presets'][] = $preset;
                } else {
                    $ungrouped[] = $preset;
                }
            } else {
                $ungrouped[] = $preset;
            }
        }

        $result = [];

        // Add grouped presets
        foreach ($grouped as $group) {
            $result[] = $group;
        }

        // Add ungrouped presets as a single group if any exist
        if (! empty($ungrouped)) {
            $result[] = [
                'name' => 'ungrouped',
                'label' => 'Other',
                'presets' => $ungrouped,
            ];
        }

        return $result;
    }

    /**
     * Retrieve raw preset definitions, keyed by resolved preset key.
     */
    public static function getPresetViewDefinitions(): array
    {
        $tableConfig = static::table();
        $presets = $tableConfig['preset_views'] ?? [];
        $resolved = [];

        foreach ($presets as $key => $definition) {
            if ($definition === null) {
                continue;
            }

            $resolvedKey = null;

            if (is_array($definition)) {
                $resolvedKey = $definition['key'] ?? (is_string($key) ? $key : null);
                if (! $resolvedKey) {
                    continue;
                }

                $definition['key'] = $resolvedKey;
            } elseif (is_object($definition) && method_exists($definition, 'key')) {
                $resolvedKey = $definition->key();
            } elseif (is_string($definition) && $definition !== '') {
                $resolvedKey = $definition;
                $definition = ['key' => $resolvedKey];
            }

            if (! $resolvedKey) {
                continue;
            }

            $resolved[$resolvedKey] = $definition;
        }

        return $resolved;
    }

    /**
     * Resolve a preset definition by key.
     */
    public static function findPresetDefinition(?string $key): ?array
    {
        if (! $key) {
            return null;
        }

        $definitions = static::getPresetViewDefinitions();

        return $definitions[$key] ?? null;
    }

    public static function getFormFields(): array
    {
        $formConfig = array_values(static::form());

        return array_map(function ($field) {
            return is_object($field) && method_exists($field, 'toArray') ? $field->toArray() : $field;
        }, $formConfig);
    }

    public static function getValidationRules(): array
    {
        $fields = static::form();
        $rules = [];

        foreach ($fields as $field) {
            if (method_exists($field, 'toArray')) {
                $fieldArray = $field->toArray();

                if (! empty($fieldArray['rules'])) {
                    $rules[$fieldArray['name']] = $fieldArray['rules'];
                }
            }
        }

        return $rules;
    }

    public static function getIndexPage(): string
    {
        return static::$indexPage;
    }

    public static function getCreatePage(): string
    {
        return static::$createPage;
    }

    public static function getEditPage(): string
    {
        return static::$editPage;
    }

    public static function getShowPage(): string
    {
        return static::$showPage;
    }

    public static function handleBulkAction(string $action, $records): void
    {
        if ($action === 'delete') {
            foreach ($records as $record) {
                $record->delete();
            }
        }
    }

    public static function getTitle(): ?string
    {
        return static::$title ?? null;
    }

    public static function getSlug(): ?string
    {
        return static::$slug ?? null;
    }

    public static function getNavigationGroup(): ?string
    {
        return static::$navigationGroup ?? 'Other';
    }

    public static function getNavigationIcon(): ?string
    {
        return static::$navigationIcon ?? 'heroicon-o-cube';
    }

    public static function getNavigationSort(): int
    {
        return static::$navigationSort ?? 999;
    }

    public static function canViewInNavigation(): bool
    {
        // Check if user is authenticated
        /** @var Authenticatable|null $user */
        $user = request()->user();
        if (! $user) {
            return false;
        }

        if (! $user instanceof Authorizable) {
            return false;
        }

        // Check if user is a customer (if customer model is configured)
        $customerModel = config('inertia-resource.customer_model');
        if ($customerModel && $user instanceof $customerModel) {
            return false;
        }

        // If no model is defined, show the resource
        $model = static::getModel();
        if (! $model) {
            return true;
        }

        // Check permission
        $modelName = class_basename($model);
        $permission = 'view_any_'.\Illuminate\Support\Str::snake($modelName);

        return $user->can($permission);
    }
}

