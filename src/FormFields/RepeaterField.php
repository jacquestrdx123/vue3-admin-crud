<?php

namespace InertiaResource\FormFields;

class RepeaterField extends GenericField
{
    protected array $schema = [];

    protected ?int $minItems = null;

    protected ?int $maxItems = null;

    protected bool $collapsible = false;

    protected bool $reorderable = true;

    protected bool $deletable = true;

    protected bool $addable = true;

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'repeater';
    }

    public function schema(array $schema): self
    {
        $this->schema = $schema;

        return $this;
    }

    public function minItems(int $minItems): self
    {
        $this->minItems = $minItems;

        return $this;
    }

    public function maxItems(int $maxItems): self
    {
        $this->maxItems = $maxItems;

        return $this;
    }

    public function collapsible(bool $collapsible = true): self
    {
        $this->collapsible = $collapsible;

        return $this;
    }

    public function reorderable(bool $reorderable = true): self
    {
        $this->reorderable = $reorderable;

        return $this;
    }

    public function deletable(bool $deletable = true): self
    {
        $this->deletable = $deletable;

        return $this;
    }

    public function addable(bool $addable = true): self
    {
        $this->addable = $addable;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['schema'] = array_map(function ($field) {
            return method_exists($field, 'toArray') ? $field->toArray() : $field;
        }, $this->schema);

        $array['minItems'] = $this->minItems;
        $array['maxItems'] = $this->maxItems;
        $array['collapsible'] = $this->collapsible;
        $array['reorderable'] = $this->reorderable;
        $array['deletable'] = $this->deletable;
        $array['addable'] = $this->addable;

        return $array;
    }
}

