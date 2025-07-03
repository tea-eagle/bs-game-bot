<?php

namespace App\Telegram\Commands\Forge;

use Telegram\Bot\Api as TelegramClient;
use App\Telegram\Util\StateManager;

use App\Telegram\Commands\Forge\Enums\ForgeList;
use App\Telegram\Commands\Forge\Enums\ItemList;

class Calculate
{
    public $telegram;
    public $stateManager;

    public function __construct(TelegramClient $telegram, StateManager $stateManager)
    {
        $this->telegram = $telegram;
        $this->stateManager = $stateManager;
    }

    public function calculate($chatId)
    {
        $state = $this->stateManager->getState($chatId);
        $targetLevelFrom = isset($state['data']['level_from']) && $state['data']['level_from']
            ? (int) $state['data']['level_from']
            : null;
        $targetLevelTo = (int) $state['data']['level_to'];

        $item = ItemList::tryFrom($state['data']['item']);
        $itemCost = $item->cost();

        $forgeLevels = ForgeList::cases();

        $totalForgeCost = 0;
        $totalGoldPrice = 0;
        $itemCount = 0;
        $remainingItemValue = 0;

        foreach ($forgeLevels as $levelIndex => $forgeLevel) {
            if (!is_null($targetLevelFrom) && $targetLevelFrom > $levelIndex) {
                continue;
            }

            if ($levelIndex === $targetLevelTo) {
                break;
            }

            $forgeData = $forgeLevel->data();
            $requiredForgeCost = $forgeData['cost'];
            $currentForgeCost = $requiredForgeCost;

            // Учитываем остаток от предыдущего предмета
            if ($remainingItemValue > 0) {
                if ($remainingItemValue >= $requiredForgeCost) {
                    $totalForgeCost += $requiredForgeCost - 1;
                    $remainingItemValue -= ($requiredForgeCost - 1);
                    $currentForgeCost = 1;
                } else {
                    $totalForgeCost += $remainingItemValue;
                    $currentForgeCost -= $remainingItemValue;
                    $remainingItemValue = 0;
                }
            }

            // Пока не набрали опыт для перехода на следующий уровень
            while ($currentForgeCost > 0) {
                $itemCount++;

                // Добавляем цену предмета в золоте по текущему уровню
                $totalGoldPrice += $forgeData['price'];

                if ($itemCost >= $currentForgeCost) {
                    $remainingItemValue = $itemCost - $currentForgeCost;
                    $totalForgeCost += $currentForgeCost;
                    $currentForgeCost = 0;
                } else {
                    $currentForgeCost -= $itemCost;
                    $totalForgeCost += $itemCost;
                }
            }
        }

        return [
            'amount'      => $totalGoldPrice,
            'forge_sum'   => $totalForgeCost,
            'count_items' => $itemCount,
        ];
    }
}
