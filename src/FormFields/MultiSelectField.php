<?php

namespace InertiaResource\FormFields;

class MultiSelectField extends GenericField
{
    protected string $itemTitle = 'label';

    protected string $itemValue = 'value';

    protected ?string $relationship = null;

    protected ?string $titleAttribute = null;

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'multi-select';
    }

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function items(array $items): self
    {
        $this->options = $items;

        return $this;
    }

    public function itemTitle(string $itemTitle): self
    {
        $this->itemTitle = $itemTitle;

        return $this;
    }

    public function itemValue(string $itemValue): self
    {
        $this->itemValue = $itemValue;

        return $this;
    }

    public function relationship(string $relationship, string $titleAttribute = 'name'): self
    {
        $this->relationship = $relationship;
        $this->titleAttribute = $titleAttribute;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['itemTitle'] = $this->itemTitle;
        $array['itemValue'] = $this->itemValue;

        if ($this->relationship !== null) {
            $array['relationship'] = $this->relationship;
            $array['titleAttribute'] = $this->titleAttribute ?? 'name';
        }

        return $array;
    }
}

