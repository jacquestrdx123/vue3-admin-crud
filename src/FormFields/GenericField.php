<?php

namespace InertiaResource\FormFields;

class GenericField
{
    protected string $name;

    protected string $label;

    protected string $type = 'text';

    protected bool $required = false;

    protected array $rules = [];

    protected mixed $value = null;

    protected array $options = [];

    protected string $placeholder = '';

    protected bool $disabled = false;

    protected array $validationMessages = [];

    protected ?array $showWhen = null;

    protected ?array $hideWhen = null;

    protected array $showWhenConditions = [];

    protected array $hideWhenConditions = [];

    protected mixed $min = null;

    protected mixed $max = null;

    protected mixed $step = null;

    protected mixed $after = null;

    protected mixed $before = null;

    protected mixed $default = null;

    protected int $columnSpan = 12;

    public function __construct(string $name, string $label)
    {
        $this->name = $name;
        $this->label = $label;
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function type(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;
        if ($required) {
            $this->rules[] = 'required';
        }

        return $this;
    }

    public function rules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);

        return $this;
    }

    public function value(mixed $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function default(mixed $default): self
    {
        $this->default = $default;

        return $this;
    }

    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function validationMessages(array $messages): self
    {
        $this->validationMessages = $messages;

        return $this;
    }

    public function showWhen(string $field, string $operator, mixed $value): self
    {
        $this->showWhen = $this->normalizeCondition($field, $operator, $value);

        return $this;
    }

    public function visibleWhen(string $field, string $operator, mixed $value): self
    {
        return $this->showWhen($field, $operator, $value);
    }

    public function hideWhen(string $field, string $operator, mixed $value): self
    {
        $this->hideWhen = $this->normalizeCondition($field, $operator, $value);

        return $this;
    }

    public function hiddenWhen(string $field, string $operator, mixed $value): self
    {
        return $this->hideWhen($field, $operator, $value);
    }

    public function showWhenAll(array $conditions): self
    {
        $this->showWhenConditions = [
            'type' => 'AND',
            'conditions' => array_map([$this, 'normalizeConditionArray'], $conditions),
        ];

        return $this;
    }

    public function showWhenAny(array $conditions): self
    {
        $this->showWhenConditions = [
            'type' => 'OR',
            'conditions' => array_map([$this, 'normalizeConditionArray'], $conditions),
        ];

        return $this;
    }

    public function hideWhenAll(array $conditions): self
    {
        $this->hideWhenConditions = [
            'type' => 'AND',
            'conditions' => array_map([$this, 'normalizeConditionArray'], $conditions),
        ];

        return $this;
    }

    public function hideWhenAny(array $conditions): self
    {
        $this->hideWhenConditions = [
            'type' => 'OR',
            'conditions' => array_map([$this, 'normalizeConditionArray'], $conditions),
        ];

        return $this;
    }

    public function columnSpan(int $span): self
    {
        $this->columnSpan = $span;

        return $this;
    }

    protected function normalizeCondition(string $field, string $operator, mixed $value): array
    {
        return [
            'field' => $field,
            'operator' => $this->normalizeOperator($operator),
            'value' => $value,
        ];
    }

    protected function normalizeConditionArray(array $condition): array
    {
        return [
            'field' => $condition['field'],
            'operator' => $this->normalizeOperator($condition['operator']),
            'value' => $condition['value'],
        ];
    }

    protected function normalizeOperator(string $operator): string
    {
        $operatorMap = [
            '=' => 'equals',
            '==' => 'equals',
            '!=' => 'not_equals',
            '<>' => 'not_equals',
            '>' => 'greater_than',
            '<' => 'less_than',
            '>=' => 'greater_than_or_equal',
            '<=' => 'less_than_or_equal',
        ];

        return $operatorMap[$operator] ?? $operator;
    }

    public function toArray(): array
    {
        $array = [
            'name' => $this->name,
            'label' => $this->label,
            'type' => $this->type,
            'required' => $this->required,
            'rules' => $this->rules,
            'value' => $this->value,
            'options' => $this->options,
            'placeholder' => $this->placeholder,
            'disabled' => $this->disabled,
            'validationMessages' => $this->validationMessages,
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'after' => $this->after,
            'before' => $this->before,
            'default' => $this->default,
            'columnSpan' => $this->columnSpan,
        ];

        // Add conditional visibility data if set
        if ($this->showWhen !== null) {
            $array['show_when'] = $this->showWhen;
        }

        if ($this->hideWhen !== null) {
            $array['hide_when'] = $this->hideWhen;
        }

        if (! empty($this->showWhenConditions)) {
            $array['show_when_conditions'] = $this->showWhenConditions;
        }

        if (! empty($this->hideWhenConditions)) {
            $array['hide_when_conditions'] = $this->hideWhenConditions;
        }

        return $array;
    }
}

