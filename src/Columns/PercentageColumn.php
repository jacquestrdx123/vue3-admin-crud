<?php

namespace InertiaResource\Columns;

class PercentageColumn extends GenericColumn
{
    public function __construct(string $key, string $title)
    {
        parent::__construct($key, $title);
        $this->type = 'percentage';
    }

    public static function make(string $key, string $title): self
    {
        return new self($key, $title);
    }
}

