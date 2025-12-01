<?php

namespace InertiaResource\FormFields;

class TextareaField extends GenericField
{
    protected int $rows = 3;

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'textarea';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function rows(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['rows'] = $this->rows;

        return $array;
    }
}

