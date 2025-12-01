<?php

namespace InertiaResource\Filters;

/**
 * DateFilter - For date comparisons (before, after, between, etc.)
 *
 * Usage:
 * DateFilter::make('created_at', 'Created Date')
 *     ->mode('between') // 'before', 'after', 'between', 'on'
 *     ->default(['start' => '2024-01-01', 'end' => '2024-12-31'])
 *
 * DateFilter::make('due_date', 'Due Date')
 *     ->mode('after')
 *     ->default('2024-01-01')
 *
 * Note: The actual query logic is handled in the controller's applyFilters() method
 */
class DateFilter extends Filter
{
    protected string $mode = 'between'; // 'before', 'after', 'between', 'on'

    protected ?string $startLabel = 'Start Date';

    protected ?string $endLabel = 'End Date';

    public function __construct()
    {
        $this->type = 'date';
    }

    public static function make(string $name, string $label): self
    {
        $instance = new self;
        $instance->name = $name;
        $instance->label = $label;

        return $instance;
    }

    public function mode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function before(): self
    {
        $this->mode = 'before';

        return $this;
    }

    public function after(): self
    {
        $this->mode = 'after';

        return $this;
    }

    public function between(): self
    {
        $this->mode = 'between';

        return $this;
    }

    public function on(): self
    {
        $this->mode = 'on';

        return $this;
    }

    public function startLabel(string $label): self
    {
        $this->startLabel = $label;

        return $this;
    }

    public function endLabel(string $label): self
    {
        $this->endLabel = $label;

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
            'mode' => $this->mode,
            'start_label' => $this->startLabel,
            'end_label' => $this->endLabel,
        ]);
    }
}

