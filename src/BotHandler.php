<?php

namespace App\Telegram;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Objects\Update as TelegramUpdates;
use Telegram\Bot\Objects\ResponseObject;
use Telegram\Bot\Objects\Keyboard\InlineKeyboardMarkup;
use App\Telegram\Cache\RedisCache;
use App\Telegram\Util\StateManager;

use App\Telegram\Commands\UnknownCommand;

use App\Telegram\Commands\Rune\UnknownCommand as RuneUnknownCommand;
use App\Telegram\Commands\Rune\StartCommand as RuneStartCommand;
use App\Telegram\Commands\Rune\SecondCommand as RuneSecondCommand;
use App\Telegram\Commands\Rune\ThirthCommand as RuneThirthCommand;
use App\Telegram\Commands\Rune\FourthCommand as RuneFourthCommand;
use App\Telegram\Commands\Rune\FifthCommand as RuneFifthCommand;

use App\Telegram\Commands\Spirit\UnknownCommand as SpiritUnknownCommand;
use App\Telegram\Commands\Spirit\SecondCommand as SpiritSecondCommand;
use App\Telegram\Commands\Spirit\ThirthCommand as SpiritThirthCommand;

use App\Telegram\Enums\CalculatorDirection;
use App\Telegram\Enums\SpiritCalculatorAction;

class BotHandler
{
	private $telegram;
    private $updates;
    private $cache;
    protected StateManager $stateManager;

    public function __construct(TelegramClient $telegram, RedisCache $cache)
    {
        $this->telegram = $telegram;
        $this->cache = $cache;
        $this->stateManager = CONTAINER->get(StateManager::class);
    }

    private function getUpdates()
    {
        $this->updates = $this->telegram->getWebhookUpdate();
        // $this->updates = new ResponseObject(getMockMessage());
        return $this->updates;
    }

    public function handle() {
        $updates = $this->getUpdates();
        $chatId = $updates['message']['chat']['id'] ?? $updates['callback_query']['message']['chat']['id'];
        if ($this->checkChat($updates)) {
            $command = $this->getCommand($updates);
            $this->executeCommand($command, $updates, $chatId);
        }
    }

    private function checkChat(ResponseObject $updates): bool
    {
        $messageData = isset($updates['callback_query']) ? $updates['callback_query'] : $updates;
        $chatId = $messageData['message']['chat']['id'];
        $isForum = $messageData['message']['chat']['is_forum'] ?? false;
        $chatThreadId = $isForum ? $messageData['message']['message_thread_id'] : null;

        $definedForumThemes = CONTAINER->get('config.defined_forum_themes');
        if ($definedForumThemes) {
            foreach ($definedForumThemes as $definedForumTheme) {
                if (
                    $chatId === $definedForumTheme['chat']
                    && $chatThreadId !== $definedForumTheme['room']
                ) {
                    // save_log_out('check chat false. this chat of defined chat list');
                    return false;
                }
            }
        }

        return true;
    }

    private function getCommand(ResponseObject $updates): string
    {
        $messageData = isset($updates['callback_query']) ? $updates['callback_query'] : $updates;
        $chatId = $messageData['message']['chat']['id'];
        $messageText = $messageData['message']['text'];
        $callbackData = isset($updates['callback_query']) ? $updates['callback_query']['data'] : null;

        $messageText = explode('@', $messageText);
        $messageText = $messageText[0];

        $state = $this->stateManager->getState($chatId);

        $isBotFunc = function () use ($updates) {
            foreach ($updates['message']['entities'] as $item) {
                if ($item['type'] === 'bot_command') {
                    return true;
                }
            }
            return false;
        };

        if (isset($updates['message']['entities']) && $isBotFunc()) {
            // Определяем направление калькулятора
            if ($direction = CalculatorDirection::tryFrom($messageText)) {
                $this->stateManager->setState($chatId, [
                    'current_direction' => $direction->value,
                    'current_step' => null,
                    'data' => [],
                    'current_page' => 1
                ]);
                return $direction->value;
            }

            // Определяем действие в /spirit
            if (isset($state['current_direction']) && $state['current_direction'] === CalculatorDirection::SPIRIT->value) {
                $action = SpiritCalculatorAction::fromCallback($callbackData);

                return match ($action) {
                    SpiritCalculatorAction::PREV_PAGE => $this->updatePage($chatId, -1),
                    SpiritCalculatorAction::NEXT_PAGE => $this->updatePage($chatId, +1),
                    SpiritCalculatorAction::CORE => SpiritCalculatorAction::CORE->value,
                    SpiritCalculatorAction::RESULT => SpiritCalculatorAction::RESULT->value,
                    default => 'unknown'
                };
            }
        } else {
            if (isset($state['current_direction']) && $state['current_direction'] === CalculatorDirection::SPIRIT->value) {
                $action = SpiritCalculatorAction::fromCallback($callbackData);

                return match ($action) {
                    SpiritCalculatorAction::PREV_PAGE => $this->updatePage($chatId, -1),
                    SpiritCalculatorAction::NEXT_PAGE => $this->updatePage($chatId, +1),
                    SpiritCalculatorAction::CORE => SpiritCalculatorAction::CORE->value,
                    SpiritCalculatorAction::RESULT => SpiritCalculatorAction::RESULT->value,
                    default => 'unknown'
                };
            }
        }

        // Если в состоянии есть выбранное направление, смотрим на текущий шаг
        if (isset($state['current_direction'], $state['current_step'])) {
            return $state['current_step'];
        }

        return 'unknown';
    }

    private function updatePage(int $chatId, int $delta): string
    {
        $state = $this->stateManager->getState($chatId);
        $state['current_page'] = max(1, ($state['current_page'] ?? 1) + $delta);
        $this->stateManager->setState($chatId, $state);
        return 'default';
    }

    private function executeCommand(string $command, ResponseObject $updates, int $chatId): void
    {
        $state = $this->stateManager->getState($chatId);
        $direction = $state['current_direction'] ?? null;

        if ($command === '/clear') {
            $this->stateManager->clearState($chatId);
            return;
        }

        // Определяем обработчик на основе направления и шага
        $handler = match ($direction) {
            '/rune' => match ($command) {
                'choose_flint' => CONTAINER->get(RuneSecondCommand::class, [
                    'updates' => $updates,
                ]),
                'choose_stat' => CONTAINER->get(RuneThirthCommand::class, [
                    'updates' => $updates,
                ]),
                'choose_ability_rune' => CONTAINER->get(RuneFourthCommand::class, [
                    'updates' => $updates,
                ]),
                'choose_rune' => CONTAINER->get(RuneFifthCommand::class, [
                    'updates' => $updates,
                ]),
                default => CONTAINER->get(RuneUnknownCommand::class),
            },
            '/spirit' => match ($command) {
                'choose_core' => CONTAINER->get(SpiritSecondCommand::class, [
                    'updates' => $updates,
                ]),
                'calculate_result' => CONTAINER->get(SpiritThirthCommand::class, [
                    'updates' => $updates,
                ]),
                default => CONTAINER->get(SpiritUnknownCommand::class),
            },
            default => CONTAINER->get(UnknownCommand::class),
        };

        $handler->execute($updates, $chatId, $this->stateManager);
    }
}
