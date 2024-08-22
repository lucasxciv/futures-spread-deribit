<?php

declare(strict_types=1);

namespace FuturesSpread\Controller;

use FuturesSpread\Notification\NotificationActionType;
use FuturesSpread\Notification\NotificationMessage;
use FuturesSpread\Notification\NotificationNextStatus;
use FuturesSpread\Notification\NotificationStatus;

final readonly class NotificationController
{
    public function __construct(
        private NotificationStatus $status,
        private NotificationNextStatus $nextStatus,
        private NotificationMessage $message,
    ) {}

    /**
     * @return array{message: string, status: array{rsiState: string, date: string, rsi: float, btc: float}}
     *
     * @throws \JsonException
     */
    public function handle(): array
    {
        if (NotificationActionType::Notify === $this->nextStatus->actionType) {
            $this->message->send($this->nextStatus, $this->status);

            $this->status->update(
                $this->nextStatus->rsiState,
                $this->nextStatus->date,
                $this->nextStatus->rsi,
                $this->nextStatus->btc
            );

            return [
                'message' => 'Sent.',
                'status' => $this->status->jsonSerialize(),
            ];
        }

        if (NotificationActionType::NotInTimeRange === $this->nextStatus->actionType) {
            $this->status->update($this->nextStatus->rsiState);

            return [
                'message' => sprintf(
                    'Not in the time range. Now is %sh, and the allowed time is from %sh to %sh.',
                    $this->nextStatus->time->format('H'),
                    NotificationNextStatus::TIME_RANGE_START,
                    NotificationNextStatus::TIME_RANGE_END,
                ),
                'status' => $this->status->jsonSerialize(),
            ];
        }

        if (NotificationActionType::AlreadyNotified === $this->nextStatus->actionType) {
            return [
                'message' => 'Already sent today.',
                'status' => $this->status->jsonSerialize(),
            ];
        }

        throw new \LogicException('Invalid action type.');
    }
}
