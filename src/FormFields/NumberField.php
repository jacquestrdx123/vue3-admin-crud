<?php

namespace InertiaResource\FormFields;

class NumberField extends GenericField
{
    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'number';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
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
}

