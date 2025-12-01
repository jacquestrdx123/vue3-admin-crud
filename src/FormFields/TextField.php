<?php

namespace InertiaResource\FormFields;

class TextField extends GenericField
{
    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'text';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }
}

