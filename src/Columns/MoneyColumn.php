<?php

namespace InertiaResource\Columns;

class MoneyColumn extends GenericColumn
{
    protected string $currency = 'R';

    protected int $decimals = 2;

    protected bool $colorize = false;

    public function __construct(string $key, string $title)
    {
        parent::__construct($key, $title);
        $this->type = 'money';
    }

    public static function make(string $key, string $title): self
    {
        return new self($key, $title);
    }

    public function currency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function decimals(int $decimals): self
    {
        $this->decimals = $decimals;

        return $this;
    }

    public function colorize(bool $colorize = true): self
    {
        $this->colorize = $colorize;

        return $this;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        $array['currency'] = $this->currency;
        $array['decimals'] = $this->decimals;
        $array['colorize'] = $this->colorize;

        return $array;
    }
}

