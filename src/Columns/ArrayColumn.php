<?php

namespace InertiaResource\Columns;

class ArrayColumn
{
    protected string $title;

    protected string $align = 'start'; // Default alignment

    protected bool $sortable = false; // Default sortability

    protected string $key;

    protected $type = 'array';

    protected $columns;

    public function __construct(string $key, string $title, $columns)
    {
        $this->key = $key;
        $this->title = $title;
        $this->columns = $columns;
    }

    public static function make(string $key, string $title, array $columns): self
    {
        return new self($key, $title, $columns);
    }

    public function align(string $align): self
    {
        $this->align = $align;

        return $this;
    }

    public function sortable(bool $sortable = true): self
    {
        $this->sortable = $sortable;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'align' => $this->align,
            'sortable' => $this->sortable,
            'key' => $this->key,
            'columns' => $this->columns,
            'type' => $this->type,
        ];
    }
}

