<?php

namespace FuturesSpread\Calculation;

enum RsiThresholdTypeSeriousMoney: int
{
    case LowerBand = 35;
    case UpperBand = 65;
}
