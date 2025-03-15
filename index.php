<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);

ini_set("error_log", "errors.log");

require __DIR__ . '/log.php';

// Пишем лог
// save_log();

use DI\ContainerBuilder;

use App\Telegram\Util\Container;
use App\Telegram\Cache\Cache;
use App\Telegram\Cache\RedisCache;

use Telegram\Bot\Api as TelegramClient;
use Telegram\Bot\Keyboard\Keyboard;

use Symfony\Component\Yaml\Yaml;

use App\Telegram\BotHandler;

require __DIR__ . '/vendor/autoload.php';

// Создаём контейнер
$containerBuilder = new ContainerBuilder();
$containerBuilder->useAutowiring(true);
$container = $containerBuilder->build();

// Парсим и добавляем в контейнер конфиги
$config = Yaml::parseFile(__DIR__ . '/config/main.yaml');

$container->set('config.bot_token', $config['telegram']['bot_token']);
$container->set('config.defined_forum_themes', $config['defined_forum_themes']);

// Загружаем `dependencies.php`, если он есть
$dependenciesFile = __DIR__ . '/dependencies.php';
if (file_exists($dependenciesFile)) {
    $dependencies = require $dependenciesFile;
    if (is_callable($dependencies)) {
        $dependencies($container);
    }
}

// Делаем контейнер глобальным
define('CONTAINER', $container);

define('CACHE_PREFIX', 'session_');

$botHandler = CONTAINER->get(BotHandler::class);
$botHandler->handle(); // Всё работает, зависимости автоматически подтянулись
