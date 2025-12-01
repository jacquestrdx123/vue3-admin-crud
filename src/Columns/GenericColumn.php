<?php

namespace InertiaResource\Columns;

class GenericColumn
{
    protected string $title;

    protected string $align = 'start'; // Default alignment

    protected bool $sortable = false; // Default sortability

    protected string $key;

    protected $type = 'text';

    public function __construct(string $key, string $title)
    {
        $this->key = $key;
        $this->title = $title;
    }

    public static function make(string $key, string $title): self
    {
        return new self($key, $title);
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
            'type' => $this->type,
        ];
    }
}

