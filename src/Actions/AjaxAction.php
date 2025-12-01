<?php

namespace InertiaResource\Actions;

class AjaxAction
{
    protected string $type;

    protected string $data;

    protected string $color = 'primary';

    protected string $icon = 'mdi-plus';

    protected array $parameters = [];

    public static function make(string $type, string $data): self
    {
        $instance = new self;
        $instance->type = $type;
        $instance->data = $data;

        return $instance;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function parameters(array $parameters): self
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'color' => $this->color,
            'icon' => $this->icon,
            'parameters' => $this->parameters,
        ];
    }
}

