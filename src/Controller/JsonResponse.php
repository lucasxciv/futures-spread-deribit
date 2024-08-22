<?php

declare(strict_types=1);

namespace FuturesSpread\Controller;

final readonly class JsonResponse
{
    public function __construct(private NotificationController $controller) {}

    public function __toString(): string
    {
        header('Content-Type: application/json');

        return json_encode($this->controller->handle(), JSON_THROW_ON_ERROR);
    }
}
