<?php

namespace InertiaResource\Filters;

/**
 * Base Filter Class
 *
 * This is the base class for all filter types in Inertia resources.
 * Use specialized filter classes for better type safety and clarity:
 *
 * - SelectFilter: Dropdown selections (field equals value)
 * - BooleanFilter: Yes/No conditions
 * - DateFilter: Date comparisons (before, after, between)
 * - NumberFilter: Numeric comparisons (>, >=, =, <=, <, between)
 * - CustomFilter: Custom views with complex logic
 * - TrashedFilter: Soft-deleted records filter
 *
 * Usage:
 * Filter::make('status', 'Status')
 *     ->select()
 *     ->options(['active' => 'Active', 'inactive' => 'Inactive'])
 *     ->default('active')
 */
class Filter
{
    protected string $name;

    protected string $label;

    protected string $type = 'select';

    protected array $options = [];

    protected mixed $default = null;

    protected ?\Closure $queryCallback = null;

    public static function make(string $name, string $label): self
    {
        $instance = new self;
        $instance->name = $name;
        $instance->label = $label;

        return $instance;
    }

    public function select(): self
    {
        $this->type = 'select';

        return $this;
    }

    public function boolean(): self
    {
        $this->type = 'boolean';

        return $this;
    }

    public function date(): self
    {
        $this->type = 'date';

        return $this;
    }

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function default(mixed $default): self
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Define custom query logic for this filter
     *
     * @param  \Closure  $callback  function($query, $value) { return $query->where(...); }
     * @return $this
     */
    public function query(\Closure $callback): self
    {
        $this->queryCallback = $callback;

        return $this;
    }

    /**
     * Get the query callback if defined
     */
    public function getQueryCallback(): ?\Closure
    {
        return $this->queryCallback;
    }

    /**
     * Check if filter has a custom query callback
     */
    public function hasQueryCallback(): bool
    {
        return $this->queryCallback !== null;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'options' => $this->options,
            'default' => $this->default,
            'has_query_callback' => $this->hasQueryCallback(),
        ];
    }
}

