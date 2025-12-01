<?php

namespace InertiaResource\FormFields;

class ToggleField extends GenericField
{
    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'toggle';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function default(mixed $default): static
    {
        $this->default = (bool) $default;

        return $this;
    }
}

