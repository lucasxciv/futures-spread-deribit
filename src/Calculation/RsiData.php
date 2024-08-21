<?php

declare(strict_types=1);


namespace FuturesSpread\Calculation;

use ArrayObject;
use InvalidArgumentException;

final class RsiData extends ArrayObject
{
    public function __construct(ArrayObject $prices, int $period = 14)
    {
        parent::__construct($this->generateRsi($prices, $period));
    }

    private function generateRsi(ArrayObject $prices, int $period): array
    {
        $arrayKeys = array_keys($prices->getArrayCopy());
        $rsiValues = [];
        $gainValues = [];
        $lossValues = [];

        if ($prices->count() < $period + 1) {
            throw new InvalidArgumentException('Array size must be at least period + 1');
        }

        // Calculate initial gain and loss for the first period
        for ($i = 1; $i <= $period; $i++) {
            $priceDiff = $prices->offsetGet($arrayKeys[$i]) - $prices->offsetGet($arrayKeys[$i - 1]);
            if ($priceDiff > 0) {
                $gainValues[] = $priceDiff;
                $lossValues[] = 0;
            } else {
                $gainValues[] = 0;
                $lossValues[] = abs($priceDiff);
            }
        }

        // Calculate average gain and average loss for the first period
        $avgGain = array_sum(array_slice($gainValues, 0, $period)) / $period;
        $avgLoss = array_sum(array_slice($lossValues, 0, $period)) / $period;

        // Calculate RS and RSI for subsequent elements
        for ($i = $period, $iMax = $prices->count(); $i < $iMax; $i++) {
            $priceDiff = $prices->offsetGet($arrayKeys[$i]) - $prices->offsetGet($arrayKeys[$i - 1]);

            if ($priceDiff > 0) {
                $gain = $priceDiff;
                $loss = 0;
            } else {
                $gain = 0;
                $loss = abs($priceDiff);
            }

            // Update average gain and average loss using smoothing factor
            $avgGain = (($avgGain * ($period - 1)) + $gain) / $period;
            $avgLoss = (($avgLoss * ($period - 1)) + $loss) / $period;

            // Calculate RS (Relative Strength)
            if ($avgLoss === 0) {
                $rs = 100; // To avoid division by zero error
            } else {
                $rs = $avgGain / $avgLoss;
            }

            // Calculate RSI (Relative Strength Index)
            $rsi = 100 - (100 / (1 + $rs));

            // Add RSI value to the result array
            $rsiValues[] = round($rsi, 2);
        }

        return $rsiValues;
    }
}
