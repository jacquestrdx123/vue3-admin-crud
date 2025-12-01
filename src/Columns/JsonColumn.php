<?php

namespace InertiaResource\Columns;

class JsonColumn extends GenericColumn
{
    protected bool $collapsed = true;

    protected ?int $maxDepth = 3;

    protected ?int $characterLimit = null;

    public function __construct(string $key, string $title)
    {
        parent::__construct($key, $title);
        $this->type = 'json';
    }

    public static function make(string $key, string $title): self
    {
        return new self($key, $title);
    }

    public function collapsed(bool $collapsed = true): self
    {
        $this->collapsed = $collapsed;

        return $this;
    }

    public function maxDepth(int $maxDepth): self
    {
        $this->maxDepth = $maxDepth;

        return $this;
    }

    public function characterLimit(int $limit): self
    {
        $this->characterLimit = $limit;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['collapsed'] = $this->collapsed;

        if ($this->maxDepth !== null) {
            $array['maxDepth'] = $this->maxDepth;
        }

        if ($this->characterLimit !== null) {
            $array['characterLimit'] = $this->characterLimit;
        }

        return $array;
    }
}

