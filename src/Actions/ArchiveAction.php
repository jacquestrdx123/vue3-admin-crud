<?php

namespace InertiaResource\Actions;

class ArchiveAction
{
    protected string $type = 'delete';

    protected string $data;

    protected string $color = 'primary';

    protected string $icon = 'mdi-pencil';

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

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'data' => $this->data,
            'color' => $this->color,
            'icon' => $this->icon,
        ];
    }
}

