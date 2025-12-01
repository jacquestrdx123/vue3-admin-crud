<?php

namespace InertiaResource\Filters;

/**
 * CustomFilter - For custom filter views and logic
 *
 * Usage:
 * CustomFilter::make('advanced_search', 'Advanced Search')
 *     ->component('Filters/AdvancedSearchFilter')
 *     ->fields([
 *         'search' => '',
 *         'category' => '',
 *         'date_range' => ['start' => null, 'end' => null],
 *     ])
 *     ->default([
 *         'search' => '',
 *         'category' => 'all',
 *     ])
 *
 * Note: The actual query logic MUST be handled in the controller's applyFilters() method
 * The component path points to a Vue component in resources/js/Components/
 */
class CustomFilter extends Filter
{
    protected ?string $component = null;

    protected array $fields = [];

    protected array $outputs = [];

    protected array $props = [];

    public function __construct()
    {
        $this->type = 'custom';
    }

    public static function make(string $name, string $label): self
    {
        $instance = new self;
        $instance->name = $name;
        $instance->label = $label;

        return $instance;
    }

    public function component(string $component): self
    {
        $this->component = $component;

        return $this;
    }

    public function fields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function outputs(array $outputs): self
    {
        $this->outputs = $outputs;

        return $this;
    }

    public function props(array $props): self
    {
        $this->props = $props;

        return $this;
    }

    public function default(mixed $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        if ($this->component !== null) {
            $array['component'] = $this->component;
        }

        if (! empty($this->fields)) {
            $array['fields'] = $this->fields;
        }

        if (! empty($this->outputs)) {
            $array['outputs'] = $this->outputs;
        }

        if (! empty($this->props)) {
            $array['props'] = $this->props;
        }

        return $array;
    }
}

