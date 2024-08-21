<?php

declare(strict_types=1);

namespace FuturesSpread\Deribit;

use ArrayObject;
use FuturesSpread\Http\HttpRequest;

final class Instruments extends ArrayObject
{
    public function __construct(private HttpRequest $httpRequest, InstrumentsFilter $filter)
    {
        parent::__construct($this->get($filter));
    }

    private function get(InstrumentsFilter $filter): array
    {
        $data = $this->httpRequest->make('https://www.deribit.com/api/v2/public/get_instruments', [
            'currency' => $filter->currency,
            'kind' => $filter->kind,
        ]);

        $instruments = [];
        foreach ($data['result'] as $instrument) {
            if ($instrument['settlement_period'] === 'perpetual' || $instrument['expiration_timestamp'] < time()) {
                continue;
            }
            $instruments = [$instrument['instrument_name'], ...$instruments];
        }

        return $instruments;
    }
}
