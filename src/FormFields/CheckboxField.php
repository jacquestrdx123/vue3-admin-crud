<?php

namespace InertiaResource\FormFields;

class CheckboxField extends GenericField
{
    protected bool $required = false;

    protected array $rules = [];

    protected bool $disabled = false;

    protected ?bool $defaultValue = false;

    protected ?string $helpText = null;

    protected ?string $color = null;

    protected ?string $bgColor = null;

    protected ?string $density = null;

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'checkbox';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;
        if ($required) {
            $this->rules[] = 'required';
        }

        return $this;
    }

    public function rules(array $rules): self
    {
        $this->rules = array_merge($this->rules, $rules);

        return $this;
    }

    public function disabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;

        return $this;
    }

    public function defaultValue(?bool $value): self
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function helpText(?string $text): self
    {
        $this->helpText = $text;

        return $this;
    }

    public function color(?string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function bgColor(?string $color): self
    {
        $this->bgColor = $color;

        return $this;
    }

    public function density(?string $density): self
    {
        $this->density = $density;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'required' => $this->required,
            'rules' => $this->rules,
            'disabled' => $this->disabled,
            'defaultValue' => $this->defaultValue,
            'helpText' => $this->helpText,
            'color' => $this->color,
            'bgColor' => $this->bgColor,
            'density' => $this->density,
        ]);
    }
}

