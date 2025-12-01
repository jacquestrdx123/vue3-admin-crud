<?php

namespace InertiaResource\Columns;

class DateColumn extends GenericColumn
{
    protected string $format = 'date'; // date, datetime, time, relative

    public function __construct(string $key, string $title)
    {
        parent::__construct($key, $title);
        $this->type = 'date';
    }

    public static function make(string $key, string $title): self
    {
        return new self($key, $title);
    }

    public function date(): self
    {
        $this->format = 'date';

        return $this;
    }

    public function dateTime(): self
    {
        $this->format = 'datetime';

        return $this;
    }

    public function time(): self
    {
        $this->format = 'time';

        return $this;
    }

    public function relative(): self
    {
        $this->format = 'relative';

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['format'] = $this->format;

        return $array;
    }
}

