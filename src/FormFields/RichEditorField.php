<?php

namespace InertiaResource\FormFields;

class RichEditorField extends GenericField
{
    protected array $toolbarButtons = [];

    protected bool $disableToolbarButtons = false;

    protected ?int $maxLength = null;

    public function __construct(string $name, string $label)
    {
        parent::__construct($name, $label);
        $this->type = 'rich_editor';
    }

    public function toolbarButtons(array $buttons): self
    {
        $this->toolbarButtons = $buttons;

        return $this;
    }

    public function disableToolbarButtons(array $buttons = []): self
    {
        $this->disableToolbarButtons = true;
        $this->toolbarButtons = $buttons;

        return $this;
    }

    public function maxLength(int $length): self
    {
        $this->maxLength = $length;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['toolbarButtons'] = $this->toolbarButtons;
        $array['disableToolbarButtons'] = $this->disableToolbarButtons;
        $array['maxLength'] = $this->maxLength;

        return $array;
    }
}

