<?php

namespace InertiaResource\Columns;

class BooleanColumn extends GenericColumn
{
    protected string $trueLabel = 'Yes';

    protected string $falseLabel = 'No';

    public function __construct(string $key, string $title)
    {
        parent::__construct($key, $title);
        $this->type = 'boolean';
    }

    public static function make(string $key, string $title): self
    {
        return new self($key, $title);
    }

    public function trueLabel(string $label): self
    {
        $this->trueLabel = $label;

        return $this;
    }

    public function falseLabel(string $label): self
    {
        $this->falseLabel = $label;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['trueLabel'] = $this->trueLabel;
        $array['falseLabel'] = $this->falseLabel;

        return $array;
    }
}

