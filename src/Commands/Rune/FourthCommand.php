<?php

namespace App\Telegram\Commands\Rune;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Cache\RedisCache;
use App\Telegram\Util\StateManager;

class FourthCommand
{
    public $telegram;
    public $cache;
    public $stateManager;

    public function __construct(TelegramClient $telegram, RedisCache $cache, StateManager $stateManager)
    {
        $this->telegram = $telegram;
        $this->cache = $cache;
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
        $replyText .= '<b>Кремень: </b>' . $state['data']['flintName'] . "\r\n";
        $replyText .= '<b>Минимальное значение статы: </b>' . $state['data']['flintStat'] . "\r\n";

        switch ($callbackData) {
            case 'usual_ability_rune':
                $runeStep = 30;
                $runeName = '🔵 Обычная руна способностей (-30)';
                break;
            case 'special_ability_rune':
                $runeStep = 50;
                $runeName = '🟣 Особая руна способностей (-50)';
                break;
            default:
                $runeStep = null;
                $runeName = null;
                break;
        }

        if (!$runeStep) {
            return;
        }

        $replyText .=  '<b>Руна способностей: </b>' . $runeName . "\r\n\r\n";

        $replyText .= 'Выберите узор';

        $reply_markup = InlineKeyboardMarkup::make()
                ->row(
                    InlineKeyboardButton::make([
                        'text' => 'Чудесный узор божественная сила',
                        'callback_data' => 'pattern_1_1',
                    ]),
                    InlineKeyboardButton::make([
                        'text' => 'Редкий узор Расплата',
                        'callback_data' => 'pattern_1_2',
                    ])
                )
                ->row(
                    InlineKeyboardButton::make([
                        'text' => 'Чудесный узор разгар боя',
                        'callback_data' => 'pattern_2_1',
                    ]),
                    InlineKeyboardButton::make([
                        'text' => 'Редкий узор Бастион',
                        'callback_data' => 'pattern_2_2',
                    ])
                )
                ->row(
                    InlineKeyboardButton::make([
                        'text' => 'Чудесный узор тиран',
                        'callback_data' => 'pattern_3_1',
                    ]),
                    InlineKeyboardButton::make([
                        'text' => 'Редкий узор Берсерк',
                        'callback_data' => 'pattern_3_2',
                    ])
                );

        $this->stateManager->setState($chatId, [
            'current_direction' => $state['current_direction'],
            'current_step' => 'choose_rune',
            'data' => array_merge($state['data'], [
                'abilityRune' => $callbackData,
                'abilityRuneName' => $runeName,
                'abilityRuneStep' => $runeStep,
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
