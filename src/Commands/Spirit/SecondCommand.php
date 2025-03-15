<?php

namespace App\Telegram\Commands\Spirit;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Util\StateManager;
use App\Telegram\Commands\Spirit\Enums\SpiritList;
use App\Telegram\Commands\Spirit\Enums\CoreList;

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

        // Формируем кнопки
        $reply_markup = InlineKeyboardMarkup::make();

        // Формируем кнопки ядер
        $cors = CoreList::cases();
        foreach ($cors as $key => $core) {
            $reply_markup->row(
                InlineKeyboardButton::make([
                    'text' => $core->label(),
                    'callback_data' => $core->value,
                ])
            );
        }

        $spirit = SpiritList::tryFrom($callbackData);

        if (!$spirit) {
            return;
        }

        // подготовка ответа и отправка
        $replyText = '👻 <b>Калькулятор духов</b>' . "\r\n\r\n";
        $replyText = '👻 <b>Дух</b>' . $spirit->label() . "\r\n\r\n";

        $replyText .= 'Выберите ядро';

        $state['current_step'] = 'calculate_result';
        $state['current_page'] = 1;
        $state['data']['spirit'] = $callbackData;

        $this->stateManager->setState($chatId, $state);

        $result = $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'reply_markup' => $reply_markup,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'HTML',
        ]));
    }
}
