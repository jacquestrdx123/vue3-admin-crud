<?php

namespace InertiaResource\FormFields;

class DateField extends GenericField
{
    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'date';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function min(string $min): self
    {
        $this->min = $min;

        return $this;
    }

    public function max(string $max): self
    {
        $this->max = $max;

        return $this;
    }

    public function after(string $field): self
    {
        $this->after = $field;

        return $this;
    }

    public function before(string $field): self
    {
        $this->before = $field;

        return $this;
    }
}

