<?php

namespace InertiaResource\FormFields;

class AnchorTagField extends GenericField
{
    protected string $url = '';

    protected bool $openInNewTab = false;

    protected string $buttonColor = 'primary';

    protected array $validButtonColors = [
        'primary', 'secondary', 'success', 'danger',
        'warning', 'info', 'light', 'dark',
    ];

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'anchor_tag';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function url(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function openInNewTab(bool $openInNewTab = true): self
    {
        $this->openInNewTab = $openInNewTab;

        return $this;
    }

    public function buttonColor(string $buttonColor): self
    {
        if (in_array($buttonColor, $this->validButtonColors)) {
            $this->buttonColor = $buttonColor;
        }

        return $this;
    }

    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function isOpenInNewTab(): bool
    {
        return $this->openInNewTab;
    }

    public function getButtonColor(): string
    {
        return $this->buttonColor;
    }

    public function getValidButtonColors(): array
    {
        return $this->validButtonColors;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['url'] = $this->url;
        $array['open_in_new_tab'] = $this->openInNewTab;
        $array['button_color'] = $this->buttonColor;
        $array['valid_button_colors'] = $this->validButtonColors;
        $array['label'] = $this->label;

        return $array;
    }
}

