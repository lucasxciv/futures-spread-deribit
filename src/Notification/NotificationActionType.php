<?php

declare(strict_types=1);

namespace FuturesSpread\Notification;

enum NotificationActionType: string
{
    case NotInTimeRange = 'NotInTimeRange';
    case AlreadyNotified = 'AlreadyNotified';
    case Notify = 'Notify';
}
