<?php

namespace InertiaResource\Filters;

/**
 * NumberFilter - For numeric comparisons
 *
 * Usage:
 * NumberFilter::make('amount', 'Amount')
 *     ->operator('>=') // '>', '>=', '=', '<=', '<', 'between'
 *     ->placeholder('Enter amount')
 *     ->step('0.01')
 *
 * NumberFilter::make('balance', 'Balance')
 *     ->between()
 *     ->min(0)
 *     ->max(1000)
 *
 * Note: The actual query logic is handled in the controller's applyFilters() method
 */
class NumberFilter extends Filter
{
    protected string $operator = '='; // '>', '>=', '=', '<=', '<', 'between'

    protected ?string $placeholder = null;

    protected ?float $step = null;

    protected ?float $min = null;

    protected ?float $max = null;

    public function __construct()
    {
        $this->type = 'number';
    }

    public static function make(string $name, string $label): self
    {
        $instance = new self;
        $instance->name = $name;
        $instance->label = $label;

        return $instance;
    }

    public function operator(string $operator): self
    {
        $this->operator = $operator;

        return $this;
    }

    public function greaterThan(): self
    {
        $this->operator = '>';

        return $this;
    }

    public function greaterThanOrEqual(): self
    {
        $this->operator = '>=';

        return $this;
    }

    public function equalTo(): self
    {
        $this->operator = '=';

        return $this;
    }

    public function lessThanOrEqual(): self
    {
        $this->operator = '<=';

        return $this;
    }

    public function lessThan(): self
    {
        $this->operator = '<';

        return $this;
    }

    public function between(): self
    {
        $this->operator = 'between';

        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function step(float $step): self
    {
        $this->step = $step;

        return $this;
    }

    public function min(float $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function max(float $max): self
    {
        $this->max = $max;

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

        $array['operator'] = $this->operator;

        if ($this->placeholder !== null) {
            $array['placeholder'] = $this->placeholder;
        }

        if ($this->step !== null) {
            $array['step'] = $this->step;
        }

        if ($this->min !== null) {
            $array['min'] = $this->min;
        }

        if ($this->max !== null) {
            $array['max'] = $this->max;
        }

        return $array;
    }
}

