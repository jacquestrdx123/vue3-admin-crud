<?php

namespace InertiaResource\Columns;

class LinkColumn extends GenericColumn
{
    public function __construct(string $key, string $title)
    {
        parent::__construct($key, $title);
        $this->type = 'link';
    }

    public static function make(string $key, string $title): self
    {
        return new self($key, $title);
    }
}

