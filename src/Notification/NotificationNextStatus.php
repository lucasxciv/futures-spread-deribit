<?php

declare(strict_types=1);

namespace FuturesSpread\Notification;

use FuturesSpread\Calculation\RsiThresholdTypeSeriousMoney;

final readonly class NotificationNextStatus
{
    public const int TIME_RANGE_START = 8;
    public const int TIME_RANGE_END = 13;

    public string $date;
    public string $dateBr;
    public float $rsi;
    public float $btc;
    public NotificationActionType $actionType;
    public NotificationRsiStateType $rsiState;

    public function __construct(
        public NotificationStatus $status,
        public \DateTimeImmutable $time,
        \ArrayObject $btcData,
        \ArrayObject $rsiDataBtc
    ) {
        $dates = array_keys($btcData->getArrayCopy());
        $this->date = end($dates);

        $this->dateBr = date('d/m/Y', strtotime($this->date));
        $this->rsi = $rsiDataBtc->offsetGet($rsiDataBtc->count() - 1);
        $this->btc = $btcData->offsetGet($this->date);

        $hasCrossedBand = $this->hasCrossedBand();
        $this->actionType = match (true) {
            !$hasCrossedBand && $this->isOutOfTimeRange() => NotificationActionType::NotInTimeRange,
            !$hasCrossedBand && $this->isAlreadyNotified() => NotificationActionType::AlreadyNotified,
            default => NotificationActionType::Notify,
        };

        $this->rsiState = match (true) {
            $this->rsi > RsiThresholdTypeSeriousMoney::UpperBand->value => NotificationRsiStateType::AboveUpperBand,
            $this->rsi < RsiThresholdTypeSeriousMoney::LowerBand->value => NotificationRsiStateType::BelowLowerBand,
            default => NotificationRsiStateType::InBetweenBands,
        };
    }

    private function hasCrossedBand(): bool
    {
        $statusWasInBetweenBands = NotificationRsiStateType::InBetweenBands === $this->status->rsiState;

        $crossedUpperBand = $statusWasInBetweenBands && $this->rsi > RsiThresholdTypeSeriousMoney::UpperBand->value;
        $crossedLowerBand = $statusWasInBetweenBands && $this->rsi < RsiThresholdTypeSeriousMoney::LowerBand->value;

        return $crossedUpperBand || $crossedLowerBand;
    }

    private function isOutOfTimeRange(): bool
    {
        $hour = (int) $this->time->format('H');

        return $hour < self::TIME_RANGE_START || $hour > self::TIME_RANGE_END;
    }

    private function isAlreadyNotified(): bool
    {
        return $this->status->date === $this->date;
    }
}
