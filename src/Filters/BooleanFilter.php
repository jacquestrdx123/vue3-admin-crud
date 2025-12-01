<?php

namespace InertiaResource\Filters;

/**
 * BooleanFilter - For yes/no conditions where a field condition is met
 *
 * Usage:
 * BooleanFilter::make('has_unallocated', 'Has Unallocated Amount')
 *     ->trueLabel('Has Unallocated')
 *     ->falseLabel('Fully Allocated')
 *     ->default(false)
 *
 * Note: The actual query logic is handled in the controller's applyFilters() method
 */
class BooleanFilter extends Filter
{
    protected string $trueLabel = 'Yes';

    protected string $falseLabel = 'No';

    public function __construct()
    {
        $this->type = 'boolean';
    }

    public static function make(string $name, string $label): self
    {
        $instance = new self;
        $instance->name = $name;
        $instance->label = $label;

        return $instance;
    }

    public function trueLabel(string $label): self
    {
        $this->trueLabel = $label;

        return $this;
    }

    public function falseLabel(string $label): self
    {
        $this->falseLabel = $label;

        return $this;
    }

    public function default(mixed $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'true_label' => $this->trueLabel,
            'false_label' => $this->falseLabel,
        ]);
    }
}

