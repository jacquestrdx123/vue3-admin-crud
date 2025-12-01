<?php

namespace InertiaResource\FormFields;

class TimeField extends GenericField
{
    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'time';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }
}

