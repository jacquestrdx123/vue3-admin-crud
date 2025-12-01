<?php

namespace InertiaResource\Columns;

class BadgeColumn extends GenericColumn
{
    protected array $colors = [];

    protected string $defaultColor = 'gray';

    public function __construct(string $key, string $title)
    {
        parent::__construct($key, $title);
        $this->type = 'badge';
    }

    public static function make(string $key, string $title): self
    {
        return new self($key, $title);
    }

    public function colors(array $colors): self
    {
        $this->colors = $colors;

        return $this;
    }

    public function defaultColor(string $color): self
    {
        $this->defaultColor = $color;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['colors'] = $this->colors;
        $array['defaultColor'] = $this->defaultColor;

        return $array;
    }
}

