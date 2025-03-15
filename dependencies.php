<?php

use DI\Container;
use Telegram\Bot\Api as TelegramClient;

return function (Container $container) {
    $container->set(TelegramClient::class, function ($container) {
        return new TelegramClient($container->get('config.bot_token'));
    });
};
