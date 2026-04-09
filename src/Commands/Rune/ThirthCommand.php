<?php

namespace App\Telegram\Commands\Rune;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Util\StateManager;

class ThirthCommand
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

        // удаляю сообщение
        try {
            $this->telegram->deleteMessage(array_filter([
                'chat_id' => $chatId,
                'message_id' => $messageId,
            ]));
        } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
            // example: Telegram error: Bad Request: message can't be deleted for everyone
        } catch (\TelegramBot\Api\Exception $e) {}

        $state = $this->stateManager->getState($chatId);

        if ($state['toDeleteMessages']) {
            $this->stateManager->removeFromState($chatId, 'toDeleteMessages');
            try {
                foreach ($state['toDeleteMessages'] as $messageId) {
                    $this->telegram->deleteMessage(array_filter([
                        'chat_id' => $chatId,
                        'message_id' => $messageId,
                    ]));
                }
                unset($state['toDeleteMessages']);
            } catch (\Telegram\Bot\Exceptions\TelegramResponseException $e) {
                // example: Telegram error: Bad Request: message can't be deleted for everyone
            } catch (\TelegramBot\Api\Exception $e) {}
        }

        $replyText = '☯ <b>Калькулятор узоров</b>' . "\r\n\r\n";
        $replyText .= '<b>Кремень: </b>' . $state['data']['flintName'];

        $messageText = intval($messageText);

        switch ($state['data']['flint']) {
            case 'wonderful_flint_of_damage':
                $stat = 'от 60 до 100';
                break;
            case 'wonderful_flint_of_darkness':
                $stat = 'от 30 до 140';
                break;
            case 'ancient_flint_of_darkness':
                $stat = 'от 40 до 160';
                break;
            default:
                $stat = null;
                break;
        }

        if (!$messageText) {
            $replyText .= "\r\n\r\n" . 'Неверное значение статы, отправьте еще раз. Допустимые значения ' . $stat;
            $resultSend = $this->telegram->sendMessage(array_filter([
                'chat_id' => $chatId,
                'text' => $replyText,
                'message_thread_id' => $chatThreadId ? $chatThreadId : null,
                'parse_mode' => 'html',
            ]));
            $this->stateManager->addToState($chatId, 'toDeleteMessages', [$resultSend['message_id']]);
            return;
        }

        switch ($state['data']['flint']) {
            case 'wonderful_flint_of_damage':
                $statMin = 60;
                $statMax = 100;
                break;
            case 'wonderful_flint_of_darkness':
                $statMin = 30;
                $statMax = 140;
                break;
            case 'ancient_flint_of_darkness':
                $statMin = 40;
                $statMax = 160;
                break;
        }

        if ($messageText < $statMin || $messageText > $statMax) {
            $replyText .= "\r\n\r\n" . 'Неверное значение статы, отправьте еще раз. Допустимые значения ' . $stat;
            $resultSend = $this->telegram->sendMessage(array_filter([
                'chat_id' => $chatId,
                'text' => $replyText,
                'message_thread_id' => $chatThreadId ? $chatThreadId : null,
                'parse_mode' => 'html',
            ]));
            $this->stateManager->addToState($chatId, 'toDeleteMessages', [$resultSend['message_id']]);
            return;
        }

        $replyText .= "\r\n" . '<b>Минимальное значение статы: </b>' . $messageText;

        $replyText .= "\r\n\r\n" . 'Выберите руну способностей';

        $reply_markup = InlineKeyboardMarkup::make()
                ->row(
                    InlineKeyboardButton::make([
                        'text' => '🔵 Обычная руна способностей (-30)',
                        'callback_data' => 'usual_ability_rune',
                    ])
                )
                ->row(
                    InlineKeyboardButton::make([
                        'text' => '🟣 Особая руна способностей (-50)',
                        'callback_data' => 'special_ability_rune',
                    ])
                );

        $this->stateManager->setState($chatId, [
            'current_direction' => $state['current_direction'],
            'current_step' => 'choose_ability_rune',
            'data' => array_merge($state['data'], [
                'flintStat' => $messageText,
                'flintStatMin' => $statMin,
                'flintStatMax' => $statMax,
            ]),
        ]);

        $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'reply_markup' => $reply_markup,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'html',
        ]));
    }
}
