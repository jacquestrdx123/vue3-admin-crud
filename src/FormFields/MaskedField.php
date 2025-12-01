<?php

namespace InertiaResource\FormFields;

class MaskedField extends GenericField
{
    protected string $mask = '';

    protected bool $showFormat = true;

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'masked';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function mask(string $mask): static
    {
        $this->mask = $mask;

        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function showFormat(bool $show = true): static
    {
        $this->showFormat = $show;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'mask' => $this->mask,
            'showFormat' => $this->showFormat,
        ]);
    }
}

