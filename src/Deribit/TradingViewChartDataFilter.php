<?php

declare(strict_types=1);

namespace FuturesSpread\Deribit;

use DateTimeImmutable;

final readonly class TradingViewChartDataFilter
{
    public function __construct(
        public DateTimeImmutable $startTimestamp,
        public DateTimeImmutable $endTimestamp,
        public string $instrumentName,
        public string $resolution,
    ) {
    }
}
