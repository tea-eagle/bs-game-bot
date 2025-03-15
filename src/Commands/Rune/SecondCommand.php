<?php

namespace App\Telegram\Commands\Rune;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Cache\RedisCache;
use App\Telegram\Util\StateManager;

class SecondCommand
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

        switch ($callbackData) {
            case 'wonderful_flint_of_damage':
                $flintName = '🔵 Чудесный кремень урона';
                break;
            case 'wonderful_flint_of_darkness':
                $flintName = '🔴 Чудесный кремень темноты';
                break;
            case 'ancient_flint_of_darkness':
                $flintName = '🟣 Древний кремень темноты';
                break;
            default:
                $flintName = null;
                break;
        }

        switch ($callbackData) {
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

        if (!$flintName) {
            return;
        }

        $replyText = '☯ <b>Калькулятор узоров</b>' . "\r\n\r\n";
        $replyText .= '<b>Выбран кремень: </b>' . $flintName . "\r\n\r\n";

        $replyText .= 'Отправьте в чат минимальное желаемое значение статы у кремня ' . $stat;

        $this->stateManager->setState($chatId, [
            'current_direction' => $state['current_direction'],
            'current_step' => 'choose_stat',
            'data' => [
                'flint' => $callbackData,
                'flintName' => $flintName,
            ],
        ]);

        $resultSend = $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'html',
        ]));

        $this->stateManager->addToState($chatId, 'toDeleteMessages', [$resultSend['message_id']]);
    }
}
