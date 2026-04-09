<?php

namespace App\Telegram\Commands\Rune;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Util\StateManager;

class UnknownCommand
{
    public $telegram;
    public $stateManager;

    public function __construct(TelegramClient $telegram, StateManager $stateManager)
    {
        $this->telegram = $telegram;
        $this->stateManager = $stateManager;
    }

    public function execute(ResponseObject $updates)
    {
        $messageData = isset($updates['callback_query']) ? $updates['callback_query'] : $updates;
        $chatId = $messageData['message']['chat']['id'];
        $messageId = $messageData['message']['message_id'];
        $messageText = $messageData['message']['text'];
        $isForum = $messageData['message']['chat']['is_forum'] ?? false;
        $chatThreadId = $isForum ? $messageData['message']['message_thread_id'] : null;
        $callbackData = isset($updates['callback_query']) ? $updates['callback_query']['data'] : null;

        $messageText = explode('@', $messageText);
        $messageText = $messageText[0];

        // удаляю сообщение
        try {
            $this->telegram->deleteMessage(array_filter([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]));
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
            // example: Telegram error: Bad Request: message can't be deleted for everyone
        } catch (\TelegramBot\Api\Exception $e) {}

        if (!isset($messageText) || $messageText !== '/rune') {
            $this->stateManager->clearState($chatId);
            return;
        }

        // подготовка ответа и отправка
        $replyText = '☯ <b>Калькулятор узоров</b>' . "\r\n\r\n";
        $replyText .= 'Выберите кремень';

        $reply_markup = InlineKeyboardMarkup::make()
                ->row(
                    InlineKeyboardButton::make([
                        'text' => '🔵 Чудесный кремень урона',
                        'callback_data' => 'wonderful_flint_of_damage',
                    ])
                )
                ->row(
                    InlineKeyboardButton::make([
                        'text' => '🔴 Чудесный кремень темноты',
                        'callback_data' => 'wonderful_flint_of_darkness',
                    ])
                )
                ->row(
                    InlineKeyboardButton::make([
                        'text' => '🟣 Древний кремень темноты',
                        'callback_data' => 'ancient_flint_of_darkness',
                    ])
                );

        $state = $this->stateManager->getState($chatId);

        $this->stateManager->setState($chatId, [
            'current_direction' => $state['current_direction'],
            'current_step' => 'choose_flint',
            'data' => [],
        ]);

        $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'reply_markup' => $reply_markup,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'HTML',
        ]));
    }
}
