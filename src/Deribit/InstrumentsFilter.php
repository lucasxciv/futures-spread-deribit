<?php

declare(strict_types=1);

namespace FuturesSpread\Deribit;

final readonly class InstrumentsFilter
{
    public function __construct(
        public string $currency,
        public string $kind,
    ) {
    }
}
