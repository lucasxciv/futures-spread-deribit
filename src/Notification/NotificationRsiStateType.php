<?php

declare(strict_types=1);


namespace FuturesSpread\Notification;

enum NotificationRsiStateType: string
{
    case AboveUpperBand = 'AboveUpperBand';
    case BelowLowerBand = 'BelowLowerBand';
    case InBetweenBands = 'InBetweenBands';
}
