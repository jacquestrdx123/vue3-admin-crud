<?php

namespace InertiaResource\Actions;

class BulkAction
{
    protected string $name;

    protected string $label;

    protected string $color = 'primary';

    protected ?string $icon = null;

    protected ?string $confirmationMessage = null;

    protected bool $requiresConfirmation = false;

    public static function make(string $name, string $label): self
    {
        $instance = new self;
        $instance->name = $name;
        $instance->label = $label;

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

    public function requiresConfirmation(string $message = 'Are you sure?'): self
    {
        $this->requiresConfirmation = true;
        $this->confirmationMessage = $message;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'label' => $this->label,
            'color' => $this->color,
            'icon' => $this->icon,
            'requiresConfirmation' => $this->requiresConfirmation,
            'confirmationMessage' => $this->confirmationMessage,
        ];
    }
}

