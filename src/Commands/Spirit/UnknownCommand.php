<?php

namespace App\Telegram\Commands\Spirit;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Util\StateManager;
use App\Telegram\Commands\Spirit\Enums\SpiritList;

class UnknownCommand
{
    public $telegram;
    public $stateManager;

    private int $perPage = 5; // Количество духов на странице

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

        if (!isset($messageText) || ($messageText !== '/spirit' && !in_array($callbackData, ['prev_page', 'next_page'], true))) {
            return;
        }

        // Cохраненный стейт
        $state = $this->stateManager->getState($chatId);
        $currentPage = $state['current_page'] ?? 1;

        // Определяем диапазон духов для отображения
        $startIndex = ($currentPage - 1) * $this->perPage;
        $spirits = SpiritList::cases();
        $spirits = array_reverse($spirits);
        $sountSpirits = count($spirits);
        $pageSpirits = array_slice($spirits, $startIndex, $this->perPage);

        // Формируем кнопки
        $reply_markup = InlineKeyboardMarkup::make();

        // Формируем кнопки духов
        foreach ($pageSpirits as $spirit) {
            $reply_markup->row(
                InlineKeyboardButton::make([
                    'text' => $spirit->label(),
                    'callback_data' => $spirit->value,
                ])
            );
        }

        // Добавляем кнопки пагинации
        $paginationButtons = [];
        if ($startIndex > 0) {
            $paginationButtons[] = InlineKeyboardButton::make([
                'text' => '⬅ Назад',
                'callback_data' => 'prev_page',
            ]);
        }
        if ($startIndex + $this->perPage < $sountSpirits) {
            $paginationButtons[] = InlineKeyboardButton::make([
                'text' => 'Вперёд ➡',
                'callback_data' => 'next_page',
            ]);
        }
        if ($paginationButtons) {
            $reply_markup->row(...$paginationButtons);
        }

        // подготовка ответа и отправка
        $replyText = '👻 <b>Калькулятор духов</b>' . "\r\n\r\n";
        $replyText .= 'Выберите духа';

        $this->stateManager->setState($chatId, [
            'current_direction' => $state['current_direction'],
            'current_step' => 'choose_core',
            'data' => [],
            'current_page' => $currentPage,
        ]);

        $result = $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'reply_markup' => $reply_markup,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'HTML',
        ]));
    }
}
