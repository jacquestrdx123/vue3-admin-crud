<?php

namespace InertiaResource\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use InertiaResource\Contracts\SearchQueryBuilder;
use InertiaResource\Inertia\InertiaResource;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Base Resource Controller for Inertia Resources
 *
 * This controller provides the foundation for all Inertia-based CRUD operations.
 * It handles standard resource operations (index, create, store, show, edit, update, destroy)
 * with automatic filtering, searching, sorting, and pagination.
 *
 * =================================================================================
 * DASHBOARD ORGANIZATION
 * =================================================================================
 *
 * Dashboard Structure:
 * --------------------
 * Specialized dashboards are organized separately from CRUD resources:
 *
 * Controllers:
 *   app/Http/Controllers/Inertia/
 *   ├── DashboardController.php (main dashboard)
 *   ├── DashboardDataController.php
 *   ├── ProformaInvoiceDashboardController.php
 *   └── SupportTicketDashboardController.php
 *
 * Vue Components:
 *   resources/js/Pages/
 *   ├── Dashboard.vue (main dashboard - root level)
 *   └── Dashboards/ (specialized dashboards)
 *       ├── ProformaInvoiceDashboard.vue
 *       └── SupportTicketDashboard.vue
 *
 * Reusable Dashboard Components:
 *   resources/js/Components/Dashboard/
 *   ├── ChartWidget.vue
 *   ├── DashboardCard.vue
 *   ├── DashboardFilters.vue
 *   ├── DateRangeFilter.vue
 *   ├── NewApplicationsAnalytics.vue
 *   ├── RenewalsAnalytics.vue
 *   ├── StatCard.vue
 *   ├── StatsCard.vue
 *   └── StatsOverview.vue
 *
 * Dashboard Controllers:
 * ----------------------
 * Dashboard controllers extend Controller (not BaseResourceController) and typically:
 * 1. Return Inertia::render() with aggregated data, statistics, and chart data
 * 2. Accept filter parameters (date ranges, product types, etc.)
 * 3. Use raw database queries or Eloquent aggregations for performance
 * 4. Handle errors gracefully with fallback empty data structures
 *
 * Example Dashboard Controller Pattern:
 *
 *   public function index(Request $request): Response
 *   {
 *       return Inertia::render('Dashboards/ProformaInvoiceDashboard', [
 *           'stats' => $this->getStats($startDate, $endDate),
 *           'chartData' => $this->getChartData($startDate, $endDate),
 *           'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
 *       ]);
 *   }
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
 * The relationship is accessed via the camelCase method in PHP (e.g., $model->mieQuery),
 * but is serialized as snake_case in arrays (e.g., $model->toArray()['mie_query']).
 *
 * This applies to ALL nested relationship columns:
 *   - member.m_code (not member.mCode)
 *   - advanced_payment.amount (not advancedPayment.amount)
 *   - proforma_invoice.total (not proformaInvoice.total)
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
 *           ->with(['member', 'cpdCategory', 'assignedUser']);
 *   }
 *
 * Guidelines:
 * - Only eager load relationships that are actually displayed in table columns
 * - Use dot notation for nested relationships: ->with(['member.primaryPPC'])
 * - Check your Filament Resource table() method to identify which relationships to load
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
 *   class MemberController extends BaseResourceController
 *   {
 *       public function getResourceClass(): string
 *       {
 *           return MemberResource::class;
 *       }
 *
 *       protected function getModel(): string
 *       {
 *           return Member::class;
 *       }
 *
 *       protected function getIndexRoute(): string
 *       {
 *           return 'vue.members.index';
 *       }
 *
 *       protected function getQuery(): Builder
 *       {
 *           return parent::getQuery()->with(['primaryPPC']);
 *       }
 *   }
 */
abstract class BaseResourceController extends Controller
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
        // Check both input() and query() to handle GET requests properly
        $search = trim((string) ($request->input('search') ?? $request->query('search', '')));

        if ($search === '') {
            return $query;
        }

        // Use SearchQueryBuilder interface if bound
        if (App::bound(SearchQueryBuilder::class)) {
            /** @var SearchQueryBuilder $searchQueryBuilder */
            $searchQueryBuilder = App::make(SearchQueryBuilder::class);

            return $searchQueryBuilder->apply($query, $request, $search);
        }

        // Fallback to enhanced search behavior
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

    protected function getSearchQuery(Builder $query, Request $request, string $search): Builder
    {
        $memberRelation = $this->resolveMemberRelation($query);
        $model = $query->getModel();

        // Check if model has a method to determine if it's a member model
        $isMemberModel = method_exists($model, 'getTable') && $model->getTable() === 'members';

        // If this IS the Member model, search directly on it
        if ($isMemberModel) {
            return $this->applyDirectMemberSearch($query, $search);
        }

        $likeOperator = $this->determineLikeOperator();

        // Check for direct member relationship first
        if ($memberRelation) {
            return $query->where(function (Builder $builder) use ($likeOperator, $search, $memberRelation) {
                $this->appendMemberSearchConstraint($builder, $likeOperator, $search, $memberRelation);
            });
        }

        // Check for nested member relationships (e.g., payment.member, application.member, member_qualification.member)
        $nestedMemberRelation = $this->resolveNestedMemberRelation($query);

        if ($nestedMemberRelation) {
            return $query->where(function (Builder $builder) use ($likeOperator, $search, $nestedMemberRelation) {
                $this->appendNestedMemberSearchConstraint($builder, $likeOperator, $search, $nestedMemberRelation);
            });
        }

        return $query;
    }

    protected function applyDirectMemberSearch(Builder $query, string $search): Builder
    {
        $likeOperator = $this->determineLikeOperator();

        return $query->where(function (Builder $builder) use ($likeOperator, $search) {
            $builder->where('first_name', $likeOperator, "%{$search}%")
                ->orWhere('last_name', $likeOperator, "%{$search}%")
                ->orWhere('m_code', $likeOperator, "%{$search}%")
                ->orWhere('legacy_m_code', $likeOperator, "%{$search}%")
                ->orWhere('email', $likeOperator, "%{$search}%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) {$likeOperator} ?", ["%{$search}%"]);
        });
    }

    protected function determineLikeOperator(): string
    {
        return config('database.default') === 'pgsql' ? 'ilike' : 'like';
    }

    protected function resolveMemberRelation(Builder $query): ?Relation
    {
        $model = $query->getModel();

        if (! method_exists($model, 'member')) {
            return null;
        }

        $relation = $model->member();

        return $relation instanceof Relation ? $relation : null;
    }

    /**
     * Resolve nested member relationships (e.g., payment.member, application.member, member_qualification.member)
     */
    protected function resolveNestedMemberRelation(Builder $query): ?array
    {
        $model = $query->getModel();

        // Common nested relationships that have member relationships
        $nestedRelations = [
            'payment' => 'member',          // PaymentAllocationTraces -> payment -> member
            'application' => 'member',      // ApplicationStepLog -> application -> member
            'member_qualification' => 'member', // MemberQualificationSubject -> member_qualification -> member
        ];

        foreach ($nestedRelations as $firstRelation => $secondRelation) {
            if (method_exists($model, $firstRelation)) {
                $firstRelationInstance = $model->{$firstRelation}();

                if ($firstRelationInstance instanceof Relation) {
                    $relatedModel = $firstRelationInstance->getRelated();

                    if (method_exists($relatedModel, $secondRelation)) {
                        $secondRelationInstance = $relatedModel->{$secondRelation}();

                        if ($secondRelationInstance instanceof Relation) {
                            return [
                                'first_relation' => $firstRelation,
                                'second_relation' => $secondRelation,
                                'first_relation_instance' => $firstRelationInstance,
                            ];
                        }
                    }
                }
            }
        }

        return null;
    }

    protected function appendMemberSearchConstraint(
        Builder $builder,
        string $likeOperator,
        string $search,
        Relation $memberRelation
    ): void {
        $builder->orWhereHas('member', function (Builder $memberQuery) use ($likeOperator, $search, $memberRelation) {
            if (method_exists($memberRelation, 'withTrashed')) {
                $memberQuery->withTrashed();
            }

            $memberQuery->where(function (Builder $memberConstraints) use ($likeOperator, $search) {
                $memberConstraints
                    ->where('first_name', $likeOperator, "%{$search}%")
                    ->orWhere('last_name', $likeOperator, "%{$search}%")
                    ->orWhere('m_code', $likeOperator, "%{$search}%")
                    ->orWhere('legacy_m_code', $likeOperator, "%{$search}%")
                    ->orWhere('email', $likeOperator, "%{$search}%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) {$likeOperator} ?", ["%{$search}%"]);
            });
        });
    }

    /**
     * Append search constraints for nested member relationships (e.g., payment.member, application.member)
     */
    protected function appendNestedMemberSearchConstraint(
        Builder $builder,
        string $likeOperator,
        string $search,
        array $nestedRelation
    ): void {
        $firstRelation = $nestedRelation['first_relation'];
        $secondRelation = $nestedRelation['second_relation'];
        $firstRelationInstance = $nestedRelation['first_relation_instance'];

        $builder->orWhereHas($firstRelation, function (Builder $firstQuery) use ($secondRelation, $likeOperator, $search, $firstRelationInstance) {
            if (method_exists($firstRelationInstance, 'withTrashed')) {
                $firstQuery->withTrashed();
            }

            $firstQuery->whereHas($secondRelation, function (Builder $memberQuery) use ($likeOperator, $search) {
                $memberQuery->where(function (Builder $memberConstraints) use ($likeOperator, $search) {
                    $memberConstraints
                        ->where('first_name', $likeOperator, "%{$search}%")
                        ->orWhere('last_name', $likeOperator, "%{$search}%")
                        ->orWhere('m_code', $likeOperator, "%{$search}%")
                        ->orWhere('legacy_m_code', $likeOperator, "%{$search}%")
                        ->orWhere('email', $likeOperator, "%{$search}%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) {$likeOperator} ?", ["%{$search}%"]);
                });
            });
        });
    }

    protected function getQuery(): Builder
    {
        $model = $this->getModel();

        return $model::query();
    }

    /**
     * Get optimized query for export with all necessary relationships eager loaded.
     * Child classes can override this to add export-specific eager loading.
     *
     * @return Builder
     */
    protected function getExportQuery(): Builder
    {
        return $this->getQuery();
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
            return method_exists($resource, 'findPresetDefinition') && $resource::findPresetDefinition($preset) !== null;
        });

        $query = $this->getQuery();

        // Always apply filters, regardless of preset
        $query = $this->applyFilters($query, $request);

        // Apply preset views after filters (presets can further narrow down filtered results)
        // Note: For multiple presets, filters are applied to each preset query before combining
        $query = $this->applyPresetViews($query, $request, $resource, $activePresets);

        // Apply search
        $query = $this->applySearch($query, $request);
        $query = $this->applySort($query, $request);

        $hydratedCustomFilters = $this->hydrateCustomFilters($customFilters, $request);
        $filterValues = $this->collectFilterValues($request, $filters, $hydratedCustomFilters);

        if (! empty($activePresets)) {
            $filterValues['presets'] = $activePresets;
        }

        // Capture raw SQL for dev environment (before pagination)
        $rawSql = null;

        if (config('app.env') === 'dev' || config('app.env') === 'local') {
            try {
                if (method_exists($query, 'toRawSql')) {
                    $rawSql = $query->toRawSql();
                } else {
                    // Fallback: use toSql() with bindings
                    $sql = $query->toSql();
                    $bindings = $query->getBindings();

                    foreach ($bindings as $binding) {
                        $value = is_numeric($binding) ? $binding : "'".addslashes((string) $binding)."'";
                        $sql = preg_replace('/\?/', $value, $sql, 1);
                    }

                    $rawSql = $sql;
                }
            } catch (\Exception $e) {
                // Silently fail - don't break the page if SQL capture fails
                $rawSql = null;
            }
        }

        $data = $query
            ->paginate($this->getPerPage())
            ->withQueryString();

        // Transform data to ensure relationships are accessible with correct keys
        // Laravel sometimes serializes camelCase relationships inconsistently (e.g., primaryPPC -> primary_p_p_c)
        // This ensures primary_ppc and secondary_ppc are always available in the serialized JSON
        $data->getCollection()->transform(function ($member) {
            if (method_exists($member, 'primaryPPC')) {
                // If relationship is loaded as primaryPPC, also set it as primary_ppc for serialization
                if ($member->relationLoaded('primaryPPC')) {
                    $ppc = $member->getRelation('primaryPPC');
                    $member->setRelation('primary_ppc', $ppc);
                }
            }

            if (method_exists($member, 'secondaryPPC')) {
                // If relationship is loaded as secondaryPPC, also set it as secondary_ppc for serialization
                if ($member->relationLoaded('secondaryPPC')) {
                    $member->setRelation('secondary_ppc', $member->getRelation('secondaryPPC'));
                }
            }

            return $member;
        });

        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
        $user = $request->user();

        // Get all columns (without user preferences) for the settings UI
        $allColumns = $resource->getColumns(null);

        $inertiaData = [
            'data' => $data,
            'columns' => $resource->getColumns($user),
            'allColumns' => $allColumns,
            'filters' => $filters,
            'customFilters' => $hydratedCustomFilters,
            'filterValues' => $filterValues,
            'actions' => $resource->getActions(),
            'bulkActions' => $resource->getBulkActions(),
            'presetViews' => method_exists($resource, 'getPresetViews') ? $resource::getPresetViews() : [],
            'activePresets' => $activePresets,
            'resourceSlug' => $resource->getSlug(),
            'title' => method_exists($resource, 'getTitle') ? $resource::getTitle() : null,
        ];

        // Add raw SQL only in dev environment
        if ($rawSql !== null) {
            $inertiaData['rawSql'] = $rawSql;
        }

        return Inertia::render($resource->getIndexPage(), $inertiaData);
    }

    public function create(): Response
    {
        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();

        return Inertia::render($resource->getCreatePage(), [
            'fields' => $resource->getFormFields(),
            'resourceSlug' => $resource->getSlug(),
            'title' => method_exists($resource, 'getTitle') ? $resource::getTitle() : null,
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
        $record = $model::withTrashed()->findOrFail($id);

        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();

        $currentTab = $request->input('current_tab', 'overview');
        $tabData = $this->loadTabData($record, $currentTab, $resource);

        return Inertia::render($resource->getShowPage(), [
            'item' => $record,
            'fields' => $resource->getFormFields(),
            'resourceSlug' => $resource->getSlug(),
            'title' => method_exists($resource, 'getTitle') ? $resource::getTitle() : null,
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
            'resourceSlug' => $resource->getSlug(),
            'title' => method_exists($resource, 'getTitle') ? $resource::getTitle() : null,
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

    /**
     * Export data directly to CSV download
     */
    public function export(Request $request): StreamedResponse
    {
        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();
        $model = $this->getModel();

        // Get report name from resource title
        $reportName = method_exists($resource, 'getTitle') ? $resource->getTitle() : ($resource->getSlug() ?? 'Data Export');
        $filename = $reportName . '_' . date('Y-m-d_His') . '.csv';

        // Build query with filters, presets, search, and sorting
        $query = $this->buildExportQuery($request);

        /** @var \Illuminate\Contracts\Auth\Authenticatable|null $user */
        $user = $request->user();

        // Get columns with user preferences applied
        $columns = $resource->getColumns($user);

        // Return streamed CSV response
        return response()->streamDownload(function () use ($query, $columns) {
            $fileHandle = fopen('php://output', 'w');

            // Write BOM for Excel UTF-8 compatibility
            fprintf($fileHandle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write header row
            $headers = [];
            foreach ($columns as $column) {
                $headers[] = $column['title'] ?? $column['key'] ?? 'Unknown';
            }
            fputcsv($fileHandle, $headers);

            // Stream data in chunks
            $processedCount = 0;
            $chunkSize = 500;

            $query->chunk($chunkSize, function ($chunk) use ($fileHandle, $columns, &$processedCount) {
                foreach ($chunk as $record) {
                    try {
                        $row = [];

                        foreach ($columns as $column) {
                            $columnKey = $column['key'] ?? null;
                            $columnType = $column['type'] ?? 'text';

                            if (! $columnKey) {
                                $row[] = '';
                                continue;
                            }

                            // Use optimized column value getter
                            $value = $this->getOptimizedColumnValue($record, $columnKey);

                            // Format value based on column type
                            $formattedValue = $this->formatColumnValue($value, $column, $columnType);

                            $row[] = $formattedValue;
                        }

                        // Write row directly to CSV
                        fputcsv($fileHandle, $row);

                        $processedCount++;
                    } catch (\Exception $e) {
                        Log::error('Error streaming record to CSV', [
                            'record_id' => $record->id ?? 'unknown',
                            'processed' => $processedCount,
                            'error' => $e->getMessage(),
                        ]);

                        continue;
                    }
                }

                // Free up memory after each chunk
                unset($chunk);
            });

            fclose($fileHandle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Build export query with all filters, presets, search, and sorting applied.
     * This is a public method to allow jobs to get the query for counting.
     *
     * @param Request $request
     * @return Builder
     */
    public function buildExportQuery(Request $request): Builder
    {
        /** @var InertiaResource $resource */
        $resource = $this->getResourceInstance();

        $activePresets = $this->resolveActivePresets($request);

        // Validate presets and remove invalid ones
        $activePresets = array_filter($activePresets, function ($preset) use ($resource) {
            return method_exists($resource, 'findPresetDefinition') && $resource::findPresetDefinition($preset) !== null;
        });

        // Get optimized query with all necessary relationships eager loaded
        $query = $this->getExportQuery();

        // Apply filters, presets, search, and sorting (same logic as index)
        $query = $this->applyFilters($query, $request);
        $query = $this->applyPresetViews($query, $request, $resource, $activePresets);
        $query = $this->applySearch($query, $request);
        $query = $this->applySortForExport($query, $request);

        return $query;
    }

    /**
     * Apply sorting for exports, skipping cached/computed columns that cannot be sorted at database level.
     * This prevents performance issues and database errors during export.
     * Child classes can override this to add resource-specific unsortable columns.
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    protected function applySortForExport(Builder $query, Request $request): Builder
    {
        $sortColumn = $request->input('sort_column');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (! $sortColumn) {
            return $query;
        }

        // List of columns that cannot be sorted at database level (cached/computed columns)
        // Child classes can override getUnsortableColumns() to add resource-specific columns
        $unsortableColumns = $this->getUnsortableColumns();

        // Skip sorting if the column is a cached/computed column
        if (in_array($sortColumn, $unsortableColumns)) {
            Log::info('Skipping sort on unsortable column during export', [
                'column' => $sortColumn,
            ]);

            return $query;
        }

        // For all other sortable columns, apply standard sorting
        return $query->orderBy($sortColumn, $sortDirection);
    }

    /**
     * Get list of unsortable columns for exports.
     * Child classes can override this to specify resource-specific unsortable columns.
     *
     * @return array
     */
    protected function getUnsortableColumns(): array
    {
        return [];
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

    /**
     * Optimized column value getter that avoids toArray() overhead.
     * Directly accesses model attributes, relationships, and accessors.
     *
     * @param mixed $record The model instance
     * @param string $columnKey The column key (e.g., 'id', 'primary_ppc.email', 'cached_designations_display')
     * @return mixed The column value
     */
    protected function getOptimizedColumnValue($record, string $columnKey): mixed
    {
        // Handle simple attributes (no dots) - direct access
        if (! str_contains($columnKey, '.')) {
            // Direct attribute access - fastest path
            if (isset($record->{$columnKey})) {
                return $record->{$columnKey};
            }

            // Try accessor for cached columns and computed attributes
            $accessorMethod = 'get' . str_replace('_', '', ucwords($columnKey, '_')) . 'Attribute';

            if (method_exists($record, $accessorMethod)) {
                return $record->{$columnKey};
            }

            // Fallback to getAttribute for other accessors
            return $record->getAttribute($columnKey);
        }

        // Handle relationship columns (e.g., 'primary_ppc.email')
        $parts = explode('.', $columnKey, 2);
        $relationNameSnake = $parts[0];
        $attributeName = $parts[1];

        // Convert snake_case to camelCase for relationship method name
        // Laravel uses camelCase for relationship methods (primaryPPC) but snake_case in serialization
        $relationNameCamel = Str::camel($relationNameSnake);

        // Also try with capitalized last part for acronyms (e.g., primary_ppc -> primaryPPC)
        // This handles cases where the last part is an acronym like PPC, ID, etc.
        $relationNameCamelAcronym = null;
        $snakeParts = explode('_', $relationNameSnake);

        if (count($snakeParts) > 1) {
            $lastPart = array_pop($snakeParts);

            // If last part is short (likely an acronym), uppercase it
            if (strlen($lastPart) <= 4) {
                $prefix = implode('', array_map('ucfirst', $snakeParts));
                $relationNameCamelAcronym = lcfirst($prefix) . strtoupper($lastPart);
            }
        }

        // Try to get the relationship using various name formats
        $relation = null;

        // Try camelCase with acronym handling first (e.g., primaryPPC)
        if ($relationNameCamelAcronym && $record->relationLoaded($relationNameCamelAcronym)) {
            $relation = $record->getRelation($relationNameCamelAcronym);
        } elseif ($record->relationLoaded($relationNameCamel)) {
            $relation = $record->getRelation($relationNameCamel);
        } elseif ($record->relationLoaded($relationNameSnake)) {
            $relation = $record->getRelation($relationNameSnake);
        } else {
            // If not loaded, try accessing via method call (Laravel handles name conversion)
            // This works even if the relationship isn't eager loaded
            try {
                if ($relationNameCamelAcronym && method_exists($record, $relationNameCamelAcronym)) {
                    $relation = $record->{$relationNameCamelAcronym}();
                } elseif (method_exists($record, $relationNameCamel)) {
                    $relation = $record->{$relationNameCamel}();
                } elseif (method_exists($record, $relationNameSnake)) {
                    $relation = $record->{$relationNameSnake}();
                }
            } catch (\Exception $e) {
                // Ignore errors and fall through to getNestedValue
            }
        }

        // If relationship is available, access the attribute
        if ($relation !== null) {
            // Handle both Eloquent models and collections
            if ($relation instanceof Model) {
                // Direct property access on relationship
                if (isset($relation->{$attributeName})) {
                    return $relation->{$attributeName};
                }

                // Try accessor on relationship
                return $relation->getAttribute($attributeName);
            }
        }

        // Relationship not loaded or not accessible - use getNestedValue as fallback
        return $this->getNestedValue($record, $columnKey);
    }

    /**
     * Get nested value from model using dot notation.
     * Handles both direct model access and array access.
     * This is a fallback method used when getOptimizedColumnValue() cannot access the value directly.
     *
     * @param  mixed  $record  The model instance
     * @param  string  $key  The dot-notation key (e.g., 'member.m_code')
     */
    protected function getNestedValue($record, string $key): mixed
    {
        if (! str_contains($key, '.')) {
            // Simple key, access directly
            if (is_object($record)) {
                return $record->{$key} ?? null;
            }

            if (is_array($record)) {
                return $record[$key] ?? null;
            }

            return null;
        }

        $parts = explode('.', $key);
        $value = $record;

        foreach ($parts as $part) {
            if ($value === null) {
                return null;
            }

            // Try object access first (for Eloquent models and relationships)
            if (is_object($value)) {
                // Handle relationship access (e.g., $record->member)
                if (method_exists($value, $part)) {
                    $value = $value->{$part};
                } elseif (isset($value->{$part})) {
                    $value = $value->{$part};
                } elseif (method_exists($value, 'getAttribute')) {
                    // Try Eloquent attribute access
                    $value = $value->getAttribute($part);
                } else {
                    return null;
                }
            } elseif (is_array($value)) {
                // Array access
                $value = $value[$part] ?? null;
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Format a column value based on its type.
     *
     * @param  mixed  $value  The raw value
     * @param  array  $column  The column definition array
     * @param  string  $type  The column type
     * @return mixed The formatted value
     */
    protected function formatColumnValue($value, array $column, string $type): mixed
    {
        // Handle null values
        if ($value === null) {
            return '';
        }

        // Handle array values (e.g., from nested relationships)
        if (is_array($value)) {
            // If it's an empty array, return empty string
            if (empty($value)) {
                return '';
            }

            // If it's an associative array with common fields, try to extract a meaningful value
            if (isset($value['name'])) {
                return (string) $value['name'];
            }

            if (isset($value['title'])) {
                return (string) $value['title'];
            }

            if (isset($value['id'])) {
                return (string) $value['id'];
            }

            // For indexed arrays, join with comma
            if (array_is_list($value)) {
                return implode(', ', array_filter(array_map(function ($item) {
                    if (is_array($item)) {
                        return $item['name'] ?? $item['title'] ?? $item['id'] ?? json_encode($item);
                    }

                    return (string) $item;
                }, $value)));
            }

            // For other arrays, JSON encode
            return json_encode($value);
        }

        switch ($type) {
            case 'date':
                return $this->formatDateValue($value, $column);

            case 'money':
                return $this->formatMoneyValue($value, $column);

            case 'boolean':
                return $this->formatBooleanValue($value, $column);

            case 'badge':
                // Badge columns just return the value as-is
                return $value;

            case 'text':
            default:
                // Text columns return as string
                return (string) $value;
        }
    }

    /**
     * Format a date value based on column format.
     *
     * @param  mixed  $value  The date value
     * @param  array  $column  The column definition
     * @return string Formatted date string
     */
    protected function formatDateValue($value, array $column): string
    {
        if (! $value) {
            return '';
        }

        try {
            $format = $column['format'] ?? 'date';
            $carbon = Carbon::parse($value);

            return match ($format) {
                'datetime' => $carbon->format('Y-m-d H:i:s'),
                'time' => $carbon->format('H:i:s'),
                'date' => $carbon->format('Y-m-d'),
                default => $carbon->format('Y-m-d'),
            };
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    /**
     * Format a money value.
     *
     * @param  mixed  $value  The money value (typically stored in cents)
     * @param  array  $column  The column definition
     * @return float|int Formatted money value (divide by 100 if stored in cents)
     */
    protected function formatMoneyValue($value, array $column): float|int
    {
        // Money values are typically stored in cents, so divide by 100
        // But check if it's already in the correct format
        if (is_numeric($value)) {
            // If value is very large (likely in cents), divide by 100
            // Otherwise assume it's already in the correct format
            $numericValue = (float) $value;

            if ($numericValue > 1000 || abs($numericValue) > 1000) {
                return $numericValue / 100;
            }

            return $numericValue;
        }

        return 0;
    }

    /**
     * Format a boolean value.
     *
     * @param  mixed  $value  The boolean value
     * @param  array  $column  The column definition
     * @return string Formatted boolean label
     */
    protected function formatBooleanValue($value, array $column): string
    {
        $boolValue = (bool) $value;
        $trueLabel = $column['trueLabel'] ?? 'Yes';
        $falseLabel = $column['falseLabel'] ?? 'No';

        return $boolValue ? $trueLabel : $falseLabel;
    }
}
