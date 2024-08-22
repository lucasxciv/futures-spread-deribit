<?php

declare(strict_types=1);

namespace FuturesSpread\Calculation;

final class SpreadData extends \ArrayObject
{
    public function __construct(\ArrayObject $perpetuals, \ArrayObject $futures)
    {
        $spreadValues = [];
        foreach ($perpetuals as $date => $perpetual) {
            $future = $futures[$date] ?? null;
            if (null === $future) {
                continue;
            }

            $spreadValues[$date] = ($future - $perpetual) / $perpetual * 100;
        }

        parent::__construct($spreadValues);
    }
}
