<?php

namespace InertiaResource\FormFields;

class GoogleMapsField extends GenericField
{
    protected array $mapOptions = [];

    protected bool $showMap = true;

    protected bool $showCoordinates = true;

    protected int $mapHeight = 300;

    protected string $apiKey = '';

    protected array $autocompleteOptions = [];

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'google-maps';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function mapOptions(array $options): self
    {
        $this->mapOptions = $options;

        return $this;
    }

    public function showMap(bool $show = true): self
    {
        $this->showMap = $show;

        return $this;
    }

    public function showCoordinates(bool $show = true): self
    {
        $this->showCoordinates = $show;

        return $this;
    }

    public function mapHeight(int $height): self
    {
        $this->mapHeight = $height;

        return $this;
    }

    public function apiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function autocompleteOptions(array $options): self
    {
        $this->autocompleteOptions = $options;

        return $this;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'mapOptions' => $this->mapOptions,
            'showMap' => $this->showMap,
            'showCoordinates' => $this->showCoordinates,
            'mapHeight' => $this->mapHeight,
            'apiKey' => $this->apiKey,
            'autocompleteOptions' => $this->autocompleteOptions,
        ]);
    }
}

