<?php

namespace InertiaResource\FormFields;

class RelationshipField extends SelectField
{
    protected ?bool $searchable = null;

    protected $modifyQueryUsing = null;

    public static function make(string $name, string $label): self
    {
        return new self($name, $label);
    }

    public function relationship(string $name, string $titleAttribute = 'name'): self
    {
        parent::relationship($name, $titleAttribute);

        return $this;
    }

    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;

        return $this;
    }

    public function modifyQueryUsing(callable $callback): self
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();
        $array['searchable'] = $this->searchable;

        return $array;
    }
}

