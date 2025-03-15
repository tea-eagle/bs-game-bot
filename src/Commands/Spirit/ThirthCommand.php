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
use App\Telegram\Commands\Spirit\Calculate;

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

        $spirit = SpiritList::tryFrom($state['data']['spirit']);

        if (!$spirit) {
            return;
        }

        $core = CoreList::tryFrom($callbackData);

        if (!$core) {
            return;
        }

        // Добавляю ядро в state
        $state['data']['core'] = $callbackData;
        $state = $this->stateManager->setState($chatId, $state);

        // подготовка ответа и отправка
        $replyText = '👻 <b>Калькулятор духов</b>' . "\r\n\r\n";
        $replyText .= '<b>Дух: </b>' . $spirit->label() . "\r\n";
        $replyText .= '<b>Ядро: </b>' . $core->label() . "\r\n\r\n";

        $calculator = CONTAINER->get(Calculate::class);
        $result = $calculator->calculate($chatId);

        $replyText .= 'Максимальное количество поглощений: <b>' . $result['absorption'] . "</b>\r\n";
        $replyText .= 'Базовый урон духа: <b>' . $result['base'] . "</b>\r\n";
        $replyText .= '➡ Минимальный урон с ядрами: <b>' . $result['min'] . "</b>\r\n";
        $replyText .= '➡ Максимальный урон с ядрами: <b>' . $result['max'] . '</b>';

        $result = $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'HTML',
        ]));

        $this->stateManager->clearState($chatId);
    }
}
