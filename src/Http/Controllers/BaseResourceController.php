<?php

namespace InertiaResource\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Inertia;
use Inertia\Response;
use InertiaResource\Contracts\SearchQueryBuilder;
use InertiaResource\Inertia\InertiaResource;

/**
 * Base Resource Controller for Inertia Resources
 *
 * This controller provides the foundation for all Inertia-based CRUD operations.
 * It handles standard resource operations (index, create, store, show, edit, update, destroy)
 * with automatic filtering, searching, sorting, and pagination.
 *
 * =================================================================================
 * IMPORTANT: Relationship Column Keys
 * =================================================================================
 *
 * When defining columns that access relationships in your InertiaResource, you MUST use snake_case
 * for relationship names, even if the relationship method is defined in camelCase.
 *
 * Why? Laravel automatically converts relationship names to snake_case when serializing models to arrays.
 *
 * Example:
 *   Model: public function mieQuery() { return $this->belongsTo(MieQuery::class); }
 *   ✅ Correct:   TextColumn::make('mie_query.request_id', 'Request ID')
 *   ❌ Incorrect: TextColumn::make('mieQuery.request_id', 'Request ID')
 *
 * =================================================================================
 * EAGER LOADING RELATIONSHIPS
 * =================================================================================
 *
 * To prevent N+1 query problems, override the getQuery() method in your controller
 * to eager load relationships that are displayed in your resource columns:
 *
 * Example:
 *
 *   protected function getQuery(): Builder
 *   {
 *       return parent::getQuery()
 *           ->with(['customer', 'cpdCategory', 'assignedUser']);
 *   }
 *
 * =================================================================================
 * EXTENDING THIS CONTROLLER
 * =================================================================================
 *
 * To create a new resource controller:
 *
 * 1. Extend BaseResourceController
 * 2. Implement required abstract methods:
 *    - getResourceClass(): Return the InertiaResource class name
 *    - getModel(): Return the Eloquent model class name
 *    - getIndexRoute(): Return the named route for the index page
 *
 * 3. Override getQuery() if relationships need eager loading
 * 4. Override loadTabData() if your show page has tabs with dynamic data
 *
 * Example:
 *
 *   class CustomerController extends BaseResourceController
 *   {
 *       protected function getResourceClass(): string
 *       {
 *           return CustomerResource::class;
 *       }
 *
 *       protected function getModel(): string
 *       {
 *           return Customer::class;
 *       }
 *
 *       protected function getIndexRoute(): string
 *       {
 *           return 'vue.customers.index';
 *       }
 *
 *       protected function getQuery(): Builder
 *       {
 *           return parent::getQuery()->with(['primaryPPC']);
 *       }
 *   }
 */
abstract class BaseResourceController extends \Illuminate\Routing\Controller
{
    abstract protected function getResourceClass(): string;

    abstract protected function getModel(): string;

    abstract protected function getIndexRoute(): string;

    protected function getPerPage(): int
    {
        return 15;
    }

    protected function applyFilters(Builder $query, Request $request): Builder
    {
        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();
        $filterObjects = $resource->getFilterObjects();

        foreach ($filterObjects as $filter) {
            // Skip custom filters (handled separately)
            if (is_array($filter) && ($filter['type'] ?? null) === 'custom') {
                continue;
            }

            // Handle object filters
            if (is_object($filter)) {
                $filterArray = method_exists($filter, 'toArray') ? $filter->toArray() : [];
                $name = $filterArray['name'] ?? null;

                if (! $name) {
                    continue;
                }

                $value = $request->input($name);

                // Skip if no value provided
                if ($value === null || $value === '') {
                    continue;
                }

                // Use custom query callback if defined
                if (method_exists($filter, 'hasQueryCallback') && $filter->hasQueryCallback()) {
                    $callback = $filter->getQueryCallback();
                    $query = $callback($query, $value);
                } else {
                    // Default behavior: simple where clause
                    $query->where($name, $value);
                }
            } elseif (is_array($filter)) {
                // Handle legacy array filters
                $name = $filter['name'] ?? null;

                if (! $name) {
                    continue;
                }

                $value = $request->input($name);

                if ($value !== null && $value !== '') {
                    $query->where($name, $value);
                }
            }
        }

        return $query;
    }

    protected function applySearch(Builder $query, Request $request): Builder
    {
        $search = trim((string) $request->input('search', ''));

        if ($search === '') {
            return $query;
        }

        // Use SearchQueryBuilder interface if bound
        if (App::bound(SearchQueryBuilder::class)) {
            /** @var SearchQueryBuilder $searchQueryBuilder */
            $searchQueryBuilder = App::make(SearchQueryBuilder::class);

            return $searchQueryBuilder->apply($query, $request, $search);
        }

        // Fallback to default search behavior (can be overridden)
        return $this->getSearchQuery($query, $request, $search);
    }

    protected function applySort(Builder $query, Request $request): Builder
    {
        $sortColumn = $request->input('sort_column');
        $sortDirection = $request->input('sort_direction', 'asc');

        if ($sortColumn) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return $query;
    }

    /**
     * Default search implementation - can be overridden in child controllers
     * or replaced by binding a SearchQueryBuilder implementation
     */
    protected function getSearchQuery(Builder $query, Request $request, string $search): Builder
    {
        // Default: search in all searchable columns
        // Override this method in child controllers for custom search logic
        return $query;
    }

    protected function getQuery(): Builder
    {
        $model = $this->getModel();

        return $model::query();
    }

    protected function getResourceInstance(): InertiaResource
    {
        $class = $this->getResourceClass();

        return new $class;
    }

    public function index(Request $request): Response
    {
        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();
        $filters = $resource->getFilters();
        $customFilters = $resource->getCustomFilters();
        $activePresets = $this->resolveActivePresets($request);

        // Validate presets and remove invalid ones
        $activePresets = array_filter($activePresets, function ($preset) use ($resource) {
            return $resource::findPresetDefinition($preset) !== null;
        });

        $query = $this->getQuery();

        // Always apply filters, regardless of preset
        $query = $this->applyFilters($query, $request);

        // Apply preset views after filters (presets can further narrow down filtered results)
        // Note: For multiple presets, filters are applied to each preset query before combining
        $query = $this->applyPresetViews($query, $request, $resource, $activePresets);
        $query = $this->applySearch($query, $request);
        $query = $this->applySort($query, $request);

        $hydratedCustomFilters = $this->hydrateCustomFilters($customFilters, $request);
        $filterValues = $this->collectFilterValues($request, $filters, $hydratedCustomFilters);
        if (! empty($activePresets)) {
            $filterValues['presets'] = $activePresets;
        }
        $data = $query
            ->paginate($this->getPerPage())
            ->withQueryString();

        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
        $user = $request->user();

        // Get all columns (without user preferences) for the settings UI
        $allColumns = $resource->getColumns(null);

        return Inertia::render($resource->getIndexPage(), [
            'data' => $data,
            'columns' => $resource->getColumns($user),
            'allColumns' => $allColumns,
            'filters' => $filters,
            'customFilters' => $hydratedCustomFilters,
            'filterValues' => $filterValues,
            'actions' => $resource->getActions(),
            'bulkActions' => $resource->getBulkActions(),
            'presetViews' => $resource::getPresetViews(),
            'activePresets' => $activePresets,
            'resourceSlug' => $resource->getSlug(),
        ]);
    }

    public function create(): Response
    {
        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();

        return Inertia::render($resource->getCreatePage(), [
            'fields' => $resource->getFormFields(),
        ]);
    }

    public function store(Request $request)
    {
        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();
        $model = $this->getModel();

        $validated = $request->validate($resource->getValidationRules());

        $record = $model::create($validated);

        return redirect()
            ->route($this->getIndexRoute())
            ->with('success', 'Record created successfully.');
    }

    public function show(Request $request, $id): Response
    {
        $model = $this->getModel();
        $record = $model::findOrFail($id);

        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();

        $currentTab = $request->input('current_tab', 'overview');
        $tabData = $this->loadTabData($record, $currentTab, $resource);

        return Inertia::render($resource->getShowPage(), [
            'item' => $record,
            'fields' => $resource->getFormFields(),
            'current_tab' => $currentTab,
            'tab_data' => $tabData,
        ]);
    }

    protected function loadTabData($record, string $tab, $resource): array
    {
        // Override this method in specific controllers to load tab-specific data
        // For now, return empty array - child controllers can implement specific logic
        if (method_exists($resource, 'getTabData')) {
            return $resource->getTabData($record, $tab);
        }

        return [];
    }

    public function edit($id): Response
    {
        $model = $this->getModel();
        $record = $model::findOrFail($id);

        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();

        return Inertia::render($resource->getEditPage(), [
            'item' => $record,
            'fields' => $resource->getFormFields(),
        ]);
    }

    public function update(Request $request, $id)
    {
        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();
        $model = $this->getModel();

        $record = $model::findOrFail($id);

        $validated = $request->validate($resource->getValidationRules());

        $record->update($validated);

        return redirect()
            ->route($this->getIndexRoute())
            ->with('success', 'Record updated successfully.');
    }

    public function destroy($id)
    {
        $model = $this->getModel();
        $record = $model::findOrFail($id);

        $record->delete();

        return redirect()
            ->route($this->getIndexRoute())
            ->with('success', 'Record deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);

        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();
        $model = $this->getModel();

        $records = $model::whereIn('id', $ids)->get();

        $resource->handleBulkAction($action, $records);

        return redirect()
            ->route($this->getIndexRoute())
            ->with('success', 'Bulk action completed successfully.');
    }

    protected function hydrateCustomFilters(array $customFilters, Request $request): array
    {
        return array_map(function ($filter) use ($request) {
            $values = [];

            if (! empty($filter['outputs']) && is_array($filter['outputs'])) {
                $filter['outputs'] = array_map(function ($output) use ($request, &$values) {
                    $name = $output['name'] ?? null;
                    if (! $name) {
                        return $output;
                    }

                    $currentValue = $request->input($name, $output['default'] ?? null);
                    $output['value'] = $currentValue;
                    $values[$name] = $currentValue;

                    return $output;
                }, $filter['outputs']);
            }

            $filter['values'] = $values;

            return $filter;
        }, $customFilters);
    }

    protected function collectFilterValues(Request $request, array $filters, array $customFilters): array
    {
        $values = [];

        foreach ($filters as $filter) {
            $value = $request->input($filter['name']);

            if ($value !== null && $value !== '') {
                $values[$filter['name']] = $value;
            }
        }

        foreach ($customFilters as $filter) {
            foreach ($filter['outputs'] ?? [] as $output) {
                $name = $output['name'] ?? null;

                if (! $name) {
                    continue;
                }

                $currentValue = $output['value'] ?? null;

                if ($currentValue !== null && $currentValue !== '') {
                    $values[$name] = $currentValue;
                }
            }
        }

        return $values;
    }

    protected function resolveActivePresets(Request $request): array
    {
        $presets = $request->input('presets', $request->input('preset'));

        // Handle backward compatibility: single preset string
        if (is_string($presets) && $presets !== '') {
            return [$presets];
        }

        // Handle array of presets
        if (is_array($presets)) {
            return array_filter(array_map(function ($preset) {
                return is_string($preset) && $preset !== '' ? $preset : null;
            }, $presets));
        }

        return [];
    }

    protected function applyPresetViews(Builder $query, Request $request, $resource, array $activePresets): Builder
    {
        if (empty($activePresets) || ! method_exists($resource, 'findPresetDefinition')) {
            return $query;
        }

        // If only one preset, apply it directly
        if (count($activePresets) === 1) {
            return $this->applySinglePreset($query, $request, $resource, $activePresets[0]);
        }

        // Multiple presets: get IDs from each preset query (with filters applied) and combine with OR
        $allIds = [];

        foreach ($activePresets as $presetKey) {
            $definition = $resource::findPresetDefinition($presetKey);

            if (! $definition) {
                continue;
            }

            $callback = $definition['query'] ?? $definition['apply'] ?? null;

            if (is_callable($callback)) {
                // Create a fresh query with filters applied (same as main query)
                $presetQuery = $this->getQuery();
                $presetQuery = $this->applyFilters($presetQuery, $request);
                $presetQuery = $callback($presetQuery, $request);

                if ($presetQuery instanceof Builder) {
                    // Get IDs that match this preset (with filters)
                    $ids = $presetQuery->pluck('id')->toArray();
                    $allIds = array_merge($allIds, $ids);
                }
            }
        }

        // Remove duplicates and apply whereIn
        $allIds = array_unique($allIds);

        if (empty($allIds)) {
            // No matches from any preset, return empty result
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $allIds);
    }

    protected function applySinglePreset(Builder $query, Request $request, $resource, string $presetKey): Builder
    {
        $definition = $resource::findPresetDefinition($presetKey);

        if (! $definition) {
            return $query;
        }

        $callback = $definition['query'] ?? $definition['apply'] ?? null;

        if (is_callable($callback)) {
            $result = $callback($query, $request);

            if ($result instanceof Builder) {
                return $result;
            }
        }

        return $query;
    }
}

