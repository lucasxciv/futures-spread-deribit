<?php

declare(strict_types=1);

namespace FuturesSpread\Deribit;

use FuturesSpread\Http\HttpRequest;

/**
 * @extends \ArrayObject<string, float>
 */
final class TradingViewChartData extends \ArrayObject
{
    public function __construct(private readonly HttpRequest $httpRequest, TradingViewChartDataFilter $filter)
    {
        parent::__construct($this->get($filter));
    }

    private function get(TradingViewChartDataFilter $filter): array
    {
        $data = $this->httpRequest->make('https://www.deribit.com/api/v2/public/get_tradingview_chart_data', [
            'instrument_name' => $filter->instrumentName,
            'start_timestamp' => $filter->startTimestamp->getTimestamp() * 1000,
            'end_timestamp' => $filter->endTimestamp->getTimestamp() * 1000,
            'resolution' => $filter->resolution,
        ]);

        $iteratorData = [];
        foreach ($data['result']['ticks'] as $key => $tick) {
            $iteratorData[date('Y-m-d', (int) substr((string) $tick, 0, 10))] = $data['result']['close'][$key];
        }

        return $iteratorData;
    }
}
