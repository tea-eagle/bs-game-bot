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

class ThirdCommand
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

        $item = ItemList::tryFrom($callbackData);

        if (!$item) {
            return;
        }

        // обновляю state
        $state['data']['item'] = $callbackData;
        $this->stateManager->setState($chatId, $state);

        // подготовка ответа и отправка
        $replyText = '⚒ <b>Калькулятор ковки</b>' . "\r\n\r\n";
        $replyText .= '<b>Уровень ковки: </b>';
        if (isset($state['data']['level_from']) && $state['data']['level_from']) {
            $replyText .= ' с ' . $state['data']['level_from'] . ' по ';
        }
        $replyText .= $state['data']['level_to'] . "\r\n";
        $replyText .= '<b>Тип предмета: </b>' . $item->label() . "\r\n";

        $calculator = CONTAINER->get(Calculate::class);
        $result = $calculator->calculate($chatId);

        $forgeAmount = explode('.', $result['amount']);
        $forgeAmountGold = $forgeAmount[0];
        $forgeAmountSilver = (isset($forgeAmount[1]) && $forgeAmount[1])
            ? str_pad($forgeAmount[1], 4, '0')
            : null;

        $replyText .= '➡ Цена ковки*: <b>';
        $replyText .= $forgeAmountGold . '</b> золота';
        if (!is_null($forgeAmountSilver)) {
            $replyText .= ' <b>' . $forgeAmountSilver . '</b> серебра';
        }
        $replyText .= "\r\n";
        $replyText .= '➡ Количество предметов: <b>' . $result['count_items'] . '</b>' . "\r\n\r\n";
        $replyText .= "<i>* В цену ковки не входит стоимость приобретения предметов</i>";

        $resultSend = $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'HTML',
        ]));

        $this->stateManager->clearState($chatId);
    }

    private function formatSilver($value)
    {
        $length = strlen((string) $value);
        if ($length < 4) {
            $value *= pow(10, 4 - $length);
        }
        return $value;
    }
}
