<?php

namespace App\Telegram\Commands\Spirit;

use Telegram\Bot\Api as TelegramClient;
use App\Telegram\Util\StateManager;

use App\Telegram\Commands\Spirit\Enums\SpiritList;
use App\Telegram\Commands\Spirit\Enums\CoreList;

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

        $spirit = SpiritList::tryFrom($state['data']['spirit']);
        $core = CoreList::tryFrom($state['data']['core']);

        $datageList = $core->damage();

        return [
            'absorption' => $spirit->absorption(),
            'base'       => $spirit->damage(),
            'min'        => ($datageList['min'] * $spirit->absorption() + $spirit->damage()),
            'max'        => ($datageList['max'] * $spirit->absorption() + $spirit->damage()),
        ];
    }
}
