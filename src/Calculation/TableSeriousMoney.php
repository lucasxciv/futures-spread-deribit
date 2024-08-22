<?php

declare(strict_types=1);

namespace FuturesSpread\Calculation;

final class TableSeriousMoney extends \ArrayObject
{
    /** @var \ArrayObject<string> */
    private \ArrayObject $dates;

    public function __construct(
        \ArrayObject $spreadData,
        \ArrayObject $futureData,
        \ArrayObject $perpetualChartData,
        \ArrayObject $rsiBtcChartData
    ) {
        parent::__construct(
            $this->generateTableData(
                $spreadData,
                $futureData,
                $perpetualChartData,
                $rsiBtcChartData
            )
        );
    }

    private function generateTableData(
        \ArrayObject $spreadData,
        \ArrayObject $futureData,
        \ArrayObject $perpetualChartData,
        \ArrayObject $rsiBtcChartData
    ): \ArrayObject {
        $this->dates = new \ArrayObject(array_keys($spreadData->getArrayCopy()));
        $tableData = new \ArrayObject();

        foreach ($this->dates as $key => $date) {
            $rsiValue = $rsiBtcChartData->offsetGet($key);
            $previousRow = $this->getPreviousRow($tableData);

            $strategy = $this->evaluateStrategy($key, $rsiValue, $previousRow);

            if (StrategyTypeSeriousMoney::Skip === $strategy) {
                continue;
            }

            $tableData->append(
                new TableRowSeriousMoney(
                    previousRow: $previousRow,
                    strategy: $strategy,
                    date: $date,
                    perpetualData: $perpetualChartData->offsetGet($date),
                    futureData: $futureData->offsetGet($date)
                )
            );
        }

        return $tableData;
    }

    private function evaluateStrategy(int $index, float $rsiValue, ?TableRowSeriousMoney $previousRow): StrategyTypeSeriousMoney
    {
        if ($index === $this->dates->count() - 1) {
            return StrategyTypeSeriousMoney::Wait;
        }

        if ((!$previousRow || StrategyTypeSeriousMoney::Sell === $previousRow->strategy)
            && $rsiValue < RsiThresholdTypeSeriousMoney::LowerBand->value
        ) {
            return StrategyTypeSeriousMoney::Buy;
        }

        if ((!$previousRow || StrategyTypeSeriousMoney::Buy === $previousRow?->strategy)
            && $rsiValue > RsiThresholdTypeSeriousMoney::UpperBand->value
        ) {
            return StrategyTypeSeriousMoney::Sell;
        }

        return StrategyTypeSeriousMoney::Skip;
    }

    private function getPreviousRow(\ArrayObject $tableData): ?TableRowSeriousMoney
    {
        return $tableData->count() > 0 ? $tableData->offsetGet($tableData->count() - 1) : null;
    }
}
