<?php

declare(strict_types=1);

namespace FuturesSpread\Notification;

use FuturesSpread\View\NumberBrFormat;
use Telegram\Bot\Api;

final readonly class NotificationMessage
{
    public function __construct(private Api $telegram, private string $chatId) {}

    public function send(NotificationNextStatus $nextStatus, NotificationStatus $status): void
    {
        $this->telegram->sendMessage(
            [
                'chat_id' => $this->chatId,
                'text' => $this->buildTemplate($nextStatus, $status),
                'parse_mode' => 'HTML',
            ]
        );
    }

    private function buildTemplate(NotificationNextStatus $nextStatus, NotificationStatus $status): string
    {
        $emojiBtc = $nextStatus->btc >= $status->btc ? ' ▲' : ' 🔻';
        $emojiRsi = $nextStatus->rsi >= $status->rsi ? ' ▲' : ' 🔻';

        $btcMoney = new NumberBrFormat($nextStatus->btc);

        return <<<HTML
        Monitoramento de BTC e RSI 📈
        
        Data: <b>{$nextStatus->dateBr}</b>
        RSI: <b>{$nextStatus->rsi}</b>{$emojiRsi}
        BTC: <b>\${$btcMoney}</b>{$emojiBtc}
        
        Via: https://deribit-tooling.fly.dev
        HTML;
    }
}
