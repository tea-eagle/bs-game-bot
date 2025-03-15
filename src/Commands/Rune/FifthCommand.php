<?php

namespace App\Telegram\Commands\Rune;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardButton;
use App\Telegram\Cache\RedisCache;
use App\Telegram\Util\StateManager;

class FifthCommand
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
        $replyText .= '<b>Руна способностей: </b>' . $state['data']['abilityRuneName'] . "\r\n";

        switch ($callbackData) {
            case 'pattern_1_1':
                $runeName = 'Чудесный узор божественная сила';
                $rune_base_damage = 1800;
                break;
            case 'pattern_2_1':
                $runeName = 'Чудесный узор разгар боя';
                $rune_base_damage = 1800;
                break;
            case 'pattern_3_1':
                $runeName = 'Чудесный узор тиран';
                $rune_base_damage = 1800;
                break;
            case 'pattern_1_2':
                $runeName = 'Редкий узор Расплата';
                $rune_base_damage = 2430;
                break;
            case 'pattern_2_2':
                $runeName = 'Редкий узор Бастион';
                $rune_base_damage = 2430;
                break;
            case 'pattern_3_2':
                $runeName = 'Редкий узор Берсерк';
                $rune_base_damage = 2430;
                break;
            default:
                $runeName = null;
                $rune_base_damage = null;
                break;
        }

        if (!$rune_base_damage) {
            return;
        }

        $replyText .= '<b>Узор: </b>' . $runeName . "\r\n\r\n";

        // тут расчет и вывод результата
        $replyText .= $this->calc(
            $state['data']['abilityRuneStep'],
            120,
            $state['data']['flintStat'],
            $state['data']['flintStatMax'],
            $rune_base_damage
        );

        $this->stateManager->clearState($chatId);

        $this->telegram->sendMessage(array_filter([
            'chat_id' => $chatId,
            'text' => $replyText,
            'message_thread_id' => $chatThreadId ? $chatThreadId : null,
            'parse_mode' => 'html',
        ]));
    }

    private function calc($rune_step, $desired_max_sp, $rune_min_up, $rune_max_up, $rune_base_damage = 0) {
        $start_sp = 150;
        $step_sp = 10;
        $max_steps = 90;

        $rune_step = -1 * abs($rune_step);

        $currentSP = $start_sp;
        $countSP = 0;
        for ($i = 0; $i < $max_steps; $i++) {
            if ($i === ($max_steps - 1) && ($currentSP + $step_sp) >= $desired_max_sp) {
                $currentSP += $rune_step;
                $countSP++;
            } else if ($currentSP > $desired_max_sp) {
                $currentSP += $rune_step;
                $countSP++;
            } else {
                $currentSP += $step_sp;
            }
        }

        $countRuneP = ($max_steps - $countSP);

        $minDamage = ($countRuneP * $rune_min_up) + $rune_base_damage;
        $maxDamage = ($countRuneP * $rune_max_up) + $rune_base_damage;

        $result = "➡ Расход чудесной силы узора: <b>{$currentSP}</b>\r\n";
        $result .= "➡ Количество рун способностей ({$rune_step}): <b>{$countSP}</b>\r\n";
        $result .= "➡ Количество камней усиления, применённых: <b>{$countRuneP}</b>\r\n";
        $result .= "➡ Минимальный и максимальный урон узора: <b>{$minDamage} - {$maxDamage}</b>\r\n";

        return $result;
    }
}
