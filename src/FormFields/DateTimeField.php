<?php

namespace InertiaResource\FormFields;

class DateTimeField extends GenericField
{
    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'datetime';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }
}

