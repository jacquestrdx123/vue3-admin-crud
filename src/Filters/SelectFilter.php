<?php

namespace InertiaResource\Filters;

/**
 * SelectFilter - For dropdown selections where field equals selected value
 *
 * Usage:
 * SelectFilter::make('status', 'Status')
 *     ->options([
 *         '' => 'All',
 *         'active' => 'Active',
 *         'inactive' => 'Inactive',
 *     ])
 *     ->default('active')
 *     ->searchable() // Makes the dropdown searchable
 */
class SelectFilter extends Filter
{
    protected bool $searchable = true;

    public function __construct()
    {
        $this->type = 'select';
    }

    public static function make(string $name, string $label): self
    {
        $instance = new self;
        $instance->name = $name;
        $instance->label = $label;

        return $instance;
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

    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['searchable'] = $this->searchable;

        return $array;
    }
}

