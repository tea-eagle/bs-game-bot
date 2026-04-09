<?php

use DI\Container;
use Telegram\Bot\Api as TelegramClient;
use App\Telegram\Database\MySql;

return function (Container $container) {
    $container->set(TelegramClient::class, function ($container) {
        return new TelegramClient($container->get('config.bot_token'));
    });
    $container->set(MySql::class, function ($container) {
        return new MySql(
            $container->get('config.mysql.host'),
            $container->get('config.mysql.user'),
            $container->get('config.mysql.password'),
            $container->get('config.mysql.dbname')
        );
    });
};
