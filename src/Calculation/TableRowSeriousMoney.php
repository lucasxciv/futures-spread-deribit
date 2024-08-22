<?php

declare(strict_types=1);

namespace FuturesSpread\Calculation;

final readonly class TableRowSeriousMoney
{
    public TableRowSeriousMoney $firstOperation;
    public StrategyTypeSeriousMoney $strategy;
    public string $date;
    public float $perpetual;
    public float $future;
    public float $spread;
    public float $spreadPercent;
    public float $profit;
    public float $profitPercent;
    public float $profitMonth;
    public float $profitYear;
    public int $days;
    public float $btcDiff;
    // Totals (bottom line)
    public float $totalProfit;
    public float $totalProfitPercent;
    public float $totalProfitPercentMonth;
    public float $totalProfitPercentYear;
    public int $totalDays;
    public float $totalBtcDiff;

    public function __construct(
        ?TableRowSeriousMoney $previousRow,
        StrategyTypeSeriousMoney $strategy,
        string $date,
        float $perpetualData,
        float $futureData
    ) {
        $this->firstOperation = null === $previousRow ? $this : $previousRow->firstOperation;
        $this->strategy = $strategy;
        $this->date = $date;
        $this->perpetual = $perpetualData;
        $this->future = $futureData;

        $this->spread = $this->future - $this->perpetual;

        if (!$previousRow) {
            $this->days = 0;
            $this->profit = 0;
            $this->profitPercent = 0;
            $this->profitMonth = 0;
            $this->profitYear = 0;
            $this->spreadPercent = 0;
            $this->btcDiff = 0;
            $this->totalDays = 0;
            $this->totalProfit = 0;
            $this->totalProfitPercent = 0;
            $this->totalProfitPercentMonth = 0;
            $this->totalProfitPercentYear = 0;
            $this->totalBtcDiff = 0;

            return;
        }

        $this->days = (int) abs(round((strtotime($previousRow->date) - strtotime($date)) / 86400, 2));
        $this->profit = (StrategyTypeSeriousMoney::Buy === $previousRow->strategy ? 1 : -1) * ($this->spread - $previousRow->spread);
        $this->profitPercent = $this->profit / $previousRow->perpetual * 100;
        $this->profitMonth = $this->profitPercent * 30.473 / $this->days;
        $this->profitYear = $this->profitPercent * 365.25 / $this->days;
        $this->spreadPercent = $this->spread / $this->perpetual * 100;
        $this->btcDiff = round(($this->perpetual - $previousRow->perpetual) / $previousRow->perpetual * 100, 2);

        // Totals (bottom line)
        $this->totalDays = $this->days + $previousRow->totalDays;
        if (0 === $this->totalDays) {
            $this->totalProfit = 0;
            $this->totalProfitPercent = 0;
            $this->totalProfitPercentMonth = 0;
            $this->totalProfitPercentYear = 0;
            $this->totalBtcDiff = 0;

            return;
        }
        $this->totalProfit = $this->profit + $previousRow->totalProfit;
        $this->totalProfitPercent = $this->totalProfit / $this->firstOperation->perpetual * 100;
        $this->totalProfitPercentMonth = $this->totalProfitPercent * 30.473 / $this->totalDays;
        $this->totalProfitPercentYear = $this->totalProfitPercent * 365.25 / $this->totalDays;
        $this->totalBtcDiff = round($this->btcDiff + $previousRow->totalBtcDiff, 2);
    }
}
