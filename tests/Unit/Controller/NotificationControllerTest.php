<?php

declare(strict_types=1);

namespace FuturesSpreadUnitTest\Controller;

use DateTimeImmutable;
use FuturesSpread\Controller\NotificationController;
use FuturesSpread\Notification\NotificationMessage;
use FuturesSpread\Notification\NotificationNextStatus;
use FuturesSpread\Notification\NotificationStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Telegram\Bot\Api;
use Telegram\Bot\Objects\Chat;

final class NotificationControllerTest extends TestCase
{
    #[Test]
    public function notifyInTimeRange(): void
    {
        $controller = new NotificationController(
            $status = new NotificationStatus(
                $apiTelegram = $this->createStub(Api::class),
                $chatId = '1234567890'
            ),
            new NotificationNextStatus(
                $status,
                new DateTimeImmutable('2024-01-02 08:10:00'),
                new \ArrayObject(['2024-01-01' => 30000, '2024-01-02' => 32000]),
                new \ArrayObject([50, 52])
            ),
            new NotificationMessage(
                $apiTelegram,
                $chatId
            )
        );

        $res = $controller->handle();

        $expected = [
            'message' => 'Sent.',
            'status' => [
                'rsiState' => 'InBetweenBands',
                'date' => '2024-01-02',
                'rsi' => 52.0,
                'btc' => 32000.0,
            ],
        ];
        $this->assertSame($expected, $res);
    }

    #[Test]
    public function notifyOnCrossingUpperBand(): void
    {
        $controller = new NotificationController(
            $status = new NotificationStatus(
                $apiTelegram = $this->createStub(Api::class),
                $chatId = '1234567890'
            ),
            new NotificationNextStatus(
                $status,
                new DateTimeImmutable('2024-01-02 21:10:00'),
                new \ArrayObject(['2024-01-01' => 30000, '2024-01-02' => 32000]),
                new \ArrayObject([70, 72])
            ),
            new NotificationMessage(
                $apiTelegram,
                $chatId
            )
        );

        $res = $controller->handle();

        $expected = [
            'message' => 'Sent.',
            'status' => [
                'rsiState' => 'AboveUpperBand',
                'date' => '2024-01-02',
                'rsi' => 72.0,
                'btc' => 32000.0,
            ],
        ];
        $this->assertSame($expected, $res);
    }

    #[Test]
    public function notifyOnCrossingLowerBand(): void
    {
        $controller = new NotificationController(
            $status = new NotificationStatus(
                $apiTelegram = $this->createStub(Api::class),
                $chatId = '1234567890'
            ),
            new NotificationNextStatus(
                $status,
                new DateTimeImmutable('2024-01-02 21:10:00'),
                new \ArrayObject(['2024-01-01' => 30000, '2024-01-02' => 32000]),
                new \ArrayObject([30, 28])
            ),
            new NotificationMessage(
                $apiTelegram,
                $chatId
            )
        );

        $res = $controller->handle();

        $expected = [
            'message' => 'Sent.',
            'status' => [
                'rsiState' => 'BelowLowerBand',
                'date' => '2024-01-02',
                'rsi' => 28.0,
                'btc' => 32000.0,
            ],
        ];
        $this->assertSame($expected, $res);
    }

    #[Test]
    public function notNotifyOnKeepAboveBand(): void
    {
        $storedBase64 = base64_encode(json_encode([
            'rsiState' => 'AboveUpperBand',
            'date' => '2024-01-01',
            'rsi' => 70,
            'btc' => 30000,
        ]));

        $apiTelegram = $this->createStub(Api::class);
        $apiTelegram->method('getChat')
            ->willReturn(new Chat(['description' => 'Future spread | ' . $storedBase64]));

        $controller = new NotificationController(
            $status = new NotificationStatus(
                $apiTelegram,
                $chatId = '1234567890'
            ),
            new NotificationNextStatus(
                $status,
                new DateTimeImmutable('2024-01-02 21:10:00'),
                new \ArrayObject(['2024-01-01' => 30000, '2024-01-02' => 32000]),
                new \ArrayObject([70, 72])
            ),
            new NotificationMessage(
                $apiTelegram,
                $chatId
            )
        );

        $res = $controller->handle();

        $expected = [
            'message' => 'Not in the time range. Now is 21h, and the allowed time is from 8h to 13h.',
            'status' => [
                'rsiState' => 'AboveUpperBand',
                'date' => '2024-01-01',
                'rsi' => 70.0,
                'btc' => 30000.0,
            ],
        ];
        $this->assertSame($expected, $res);
    }

    #[Test]
    public function notNotifyOnKeepLowerBand(): void
    {
        $storedBase64 = base64_encode(json_encode([
            'rsiState' => 'BelowLowerBand',
            'date' => '2024-01-01',
            'rsi' => 30,
            'btc' => 30000,
        ]));

        $apiTelegram = $this->createStub(Api::class);
        $apiTelegram->method('getChat')
            ->willReturn(new Chat(['description' => 'Future spread | ' . $storedBase64]));

        $controller = new NotificationController(
            $status = new NotificationStatus(
                $apiTelegram,
                $chatId = '1234567890'
            ),
            new NotificationNextStatus(
                $status,
                new DateTimeImmutable('2024-01-02 21:10:00'),
                new \ArrayObject(['2024-01-01' => 30000, '2024-01-02' => 32000]),
                new \ArrayObject([30, 28])
            ),
            new NotificationMessage(
                $apiTelegram,
                $chatId
            )
        );

        $res = $controller->handle();

        $expected = [
            'message' => 'Not in the time range. Now is 21h, and the allowed time is from 8h to 13h.',
            'status' => [
                'rsiState' => 'BelowLowerBand',
                'date' => '2024-01-01',
                'rsi' => 30.0,
                'btc' => 30000.0,
            ],
        ];
        $this->assertSame($expected, $res);
    }

    #[Test]
    public function notInTimeRange(): void
    {
        $controller = new NotificationController(
            $status = new NotificationStatus(
                $apiTelegram = $this->createStub(Api::class),
                $chatId = '1234567890'
            ),
            new NotificationNextStatus(
                $status,
                new DateTimeImmutable('2024-01-02 07:10:00'),
                new \ArrayObject(['2024-01-01' => 30000, '2024-01-02' => 32000]),
                new \ArrayObject([50, 52])
            ),
            new NotificationMessage(
                $apiTelegram,
                $chatId
            )
        );

        $res = $controller->handle();

        $expected = [
            'message' => 'Not in the time range. Now is 07h, and the allowed time is from 8h to 13h.',
            'status' => [
                'rsiState' => 'InBetweenBands',
                'date' => '',
                'rsi' => 0.0,
                'btc' => 0.0,
            ],
        ];
        $this->assertSame($expected, $res);
    }

    #[Test]
    public function alreadyNotified(): void
    {
        $storedBase64 = base64_encode(json_encode([
            'rsiState' => 'InBetweenBands',
            'date' => '2024-01-02',
            'rsi' => 52,
            'btc' => 32000,
        ]));

        $apiTelegram = $this->createStub(Api::class);
        $apiTelegram->method('getChat')
            ->willReturn(new Chat(['description' => 'Future spread | ' . $storedBase64]));

        $controller = new NotificationController(
            $status = new NotificationStatus(
                $apiTelegram,
                $chatId = '1234567890'
            ),
            new NotificationNextStatus(
                $status,
                new DateTimeImmutable('2024-01-02 08:10:00'),
                new \ArrayObject(['2024-01-01' => 30000, '2024-01-02' => 32000]),
                new \ArrayObject([50, 52])
            ),
            new NotificationMessage(
                $apiTelegram,
                $chatId
            )
        );

        $res = $controller->handle();

        $expected = [
            'message' => 'Already sent today.',
            'status' => [
                'rsiState' => 'InBetweenBands',
                'date' => '2024-01-02',
                'rsi' => 52.0,
                'btc' => 32000.0,
            ],
        ];
        $this->assertSame($expected, $res);
    }
}
