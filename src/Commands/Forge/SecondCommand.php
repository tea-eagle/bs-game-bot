<?php

namespace App\Telegram\Commands\Forge;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Util\StateManager;
use App\Telegram\Commands\Forge\Enums\ForgeList;
use App\Telegram\Commands\Forge\Enums\ItemList;
use App\Telegram\Commands\Forge\Calculate;

class SecondCommand
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
        $forgeLevelFrom = null;
        $forgeLevelTo = null;

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
        $forgeMaxLevel = max(array_map(fn($case) => $case->value, ForgeList::cases()));

        $forgeLevels = explode(' ', $messageText);
        if (count($forgeLevels) === 1) {
            $forgeLevelTo = (int) $forgeLevels[0];
        } else if (count($forgeLevels) === 2) {
            $forgeLevelFrom = (int) $forgeLevels[0];
            $forgeLevelTo = (int) $forgeLevels[1];
        } else {
            return;
        }

        if (!is_null($forgeLevelFrom)) {
            $forgeFrom = ForgeList::tryFrom($forgeLevelFrom);

            if (!$forgeFrom) {
                return;
            }
        }

        if ($forgeLevelTo === 0) {
            return;
        }

        $forgeTo = ForgeList::tryFrom($forgeLevelTo);

        if (!$forgeTo) {
            return;
        }

        if (!is_null($forgeLevelFrom)) {
            if ($forgeLevelFrom > $forgeTo) {
                return;
            }
        }

        if (isset($state['toDeleteMessages']) && $state['toDeleteMessages']) {
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

        // обновляю state
        if (!is_null($forgeLevelFrom)) {
            $state['data']['level_from'] = $forgeLevelFrom;
        }
        $state['data']['level_to'] = $forgeLevelTo;
        $state['current_step'] = 'choose_item';
        $this->stateManager->setState($chatId, $state);

        // подготовка ответа и отправка
        $replyText = '⚒ <b>Калькулятор ковки</b>';
        $replyText .= "\r\n\r\n" . 'Выберите тип предмета';

        // добавляю клавиатуру
        $reply_markup = InlineKeyboardMarkup::make();

        foreach (ItemList::cases() as $item) {
            $reply_markup->row(
                    InlineKeyboardButton::make([
                        'text' => $item->label(),
                        'callback_data' => $item->value,
                    ])
                );
        }

        $resultSend = $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'reply_markup' => $reply_markup,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'HTML',
        ]));
    }
}
