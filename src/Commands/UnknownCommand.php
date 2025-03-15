<?php

namespace App\Telegram\Commands;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Cache\RedisCache;

class UnknownCommand
{
    public $telegram;
    public $cache;

    public function __construct(TelegramClient $telegram, RedisCache $cache)
    {
        $this->telegram = $telegram;
        $this->cache = $cache;
    }

    public function execute(ResponseObject $updates) {
        return;
        $messageData = isset($updates['callback_query']) ? $updates['callback_query'] : $updates;
        $chatId = $messageData['message']['chat']['id'];
        $isForum = $messageData['message']['chat']['is_forum'] ?? false;
        $chatThreadId = $isForum ? $messageData['message']['message_thread_id'] : null;

        $replyText = 'Выберите действие';

        $reply_markup = InlineKeyboardMarkup::make()
            ->row(
                InlineKeyboardButton::make([
                    'text' => '☯ Калькулятор узоров',
                    'callback_data' => 'rune_calculator',
                ])
            );

        $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'reply_markup' => $reply_markup,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'html',
        ]));
    }
}
