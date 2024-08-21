<?php

declare(strict_types=1);


namespace FuturesSpread\View;

final readonly class NumberBrFormat
{
    public function __construct(private mixed $value)
    {
    }

    public function __toString(): string
    {
        return (string)(is_numeric($this->value) ? number_format($this->value, 2, ',', '.') : $this->value);
    }
}
