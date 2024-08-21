<?php

declare(strict_types=1);

namespace FuturesSpread\Calculation;

use ArrayObject;

final class PerpetualChartData extends ArrayObject
{
    public function __construct(ArrayObject $perpetuals, ArrayObject $futures)
    {
        $perpetualsChart = [];
        foreach ($perpetuals as $date => $perpetual) {
            $future = $futures[$date] ?? null;
            if ($future === null) {
                continue;
            }

            $perpetualsChart[$date] = $perpetual;
        }

        parent::__construct($perpetualsChart);
    }
}
