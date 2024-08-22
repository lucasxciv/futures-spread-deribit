<?php

declare(strict_types=1);

namespace FuturesSpread\Notification;

use Telegram\Bot\Api;
use Telegram\Bot\Objects\Chat;

class NotificationStatus implements \JsonSerializable
{
    public NotificationRsiStateType $rsiState;
    public string $date;
    public float $rsi;
    public float $btc;

    private Chat $chat;

    public function __construct(readonly Api $telegram, string $chatId)
    {
        $this->chat = $telegram->getChat(['chat_id' => $chatId]);

        $arrDescription = explode(' | ', (string) $this->chat->description);
        $status = [];
        if (isset($arrDescription[1])) {
            $status = json_decode(base64_decode($arrDescription[1]), true, 512, JSON_THROW_ON_ERROR);
        }

        if (isset($status['rsiState'])) {
            $this->rsiState = NotificationRsiStateType::tryFrom($status['rsiState']);
        } else {
            // BC break
            $this->rsiState = match (true) {
                ($status['lockUpperBand'] ?? false) => NotificationRsiStateType::AboveUpperBand,
                ($status['lockLowerBand'] ?? false) => NotificationRsiStateType::BelowLowerBand,
                default => NotificationRsiStateType::InBetweenBands,
            };
        }

        $this->date = $status['date'] ?? '';
        $this->rsi = $status['rsi'] ?? 0;
        $this->btc = $status['btc'] ?? 0;
    }

    public function update(
        ?NotificationRsiStateType $rsiStateType = null,
        ?string $date = null,
        ?float $rsi = null,
        ?float $btc = null
    ): void {
        $this->rsiState = $rsiStateType ?? $this->rsiState;
        $this->date = $date ?? $this->date;
        $this->rsi = $rsi ?? $this->rsi;
        $this->btc = $btc ?? $this->btc;

        $arrDescription = explode(' | ', (string) $this->chat->description);
        $newDescription = sprintf(
            '%s | %s',
            '' === trim($arrDescription[0]) ? 'Future spread' : $arrDescription[0],
            base64_encode(
                json_encode(
                    [
                        'rsiState' => $this->rsiState->value,
                        'date' => $this->date,
                        'rsi' => $this->rsi,
                        'btc' => $this->btc,
                    ],
                    JSON_THROW_ON_ERROR
                )
            )
        );

        if ($this->chat->description !== $newDescription) {
            $this->telegram->setChatDescription([
                'chat_id' => $this->chat->id,
                'description' => $newDescription,
            ]);
        }
    }

    /**
     * @return array{rsiState: string, date: string, rsi: float, btc: float}
     */
    public function jsonSerialize(): array
    {
        return [
            'rsiState' => $this->rsiState->value,
            'date' => $this->date,
            'rsi' => $this->rsi,
            'btc' => $this->btc,
        ];
    }
}
