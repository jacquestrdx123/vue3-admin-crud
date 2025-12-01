<?php

namespace InertiaResource\Columns;

class TextColumn extends GenericColumn
{
    protected bool $searchable = false;

    protected ?int $limit = null;

    protected ?string $wrap = null;

    public function __construct(string $key, string $title)
    {
        parent::__construct($key, $title);
        $this->type = 'text';
    }

    public static function make(string $key, string $title): self
    {
        return new self($key, $title);
    }

    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function wrap(string $wrap = 'normal'): self
    {
        $this->wrap = $wrap;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['searchable'] = $this->searchable;

        if ($this->limit !== null) {
            $array['limit'] = $this->limit;
        }

        if ($this->wrap !== null) {
            $array['wrap'] = $this->wrap;
        }

        return $array;
    }
}

