<?php

namespace App\Telegram\Commands\Forge;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Util\StateManager;
use App\Telegram\Commands\Forge\Enums\ForgeList;

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

        if (!isset($messageText)) {
            return;
        }

        // Cохраненный стейт
        $state = $this->stateManager->getState($chatId);

        $forgeMaxLevel = max(array_map(fn($case) => $case->value, ForgeList::cases()));

        // подготовка ответа и отправка
        $replyText = '⚒ <b>Калькулятор ковки</b>' . "\r\n\r\n";
        $replyText .= 'Отправьте <i>одно число</i> от <b>1</b> до <b>';
        $replyText .= $forgeMaxLevel;
        $replyText .= '</b> до какого уровня сделать расчёт. Например:' . "\r\n";
        $replyText .= '<code>20</code>' . "\r\n\r\n";
        $replyText .= 'Или <i>два числа</i> через пробел от какого и до какого уровня сделать расчёт. Например:' . "\r\n";
        $replyText .= '<code>10 20</code>';

        $state['current_step'] = 'choose_level';

        $this->stateManager->setState($chatId, $state);

        $resultSend = $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'HTML',
        ]));

        $this->stateManager->addToState($chatId, 'toDeleteMessages', [$resultSend['message_id']]);
    }
}
