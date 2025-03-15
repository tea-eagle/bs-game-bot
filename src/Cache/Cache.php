<?php

namespace App\Telegram\Cache;

abstract class Cache {
    protected $cache;

    abstract public function set($key, $value);
    abstract public function get($key);
    abstract public function getSessionKey($uniqueValue);
}
