<?php

declare(strict_types=1);

namespace FuturesSpread\Calculation;

final class RsiBtcChartData extends \ArrayObject
{
    public function __construct(\ArrayObject $rsiBtcData, \ArrayObject $spreadData)
    {
        parent::__construct(array_slice($rsiBtcData->getArrayCopy(), $rsiBtcData->count() - $spreadData->count()));
    }
}
