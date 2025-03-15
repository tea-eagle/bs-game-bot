<?php

namespace App\Telegram\Cache;

use Redis;

class RedisCache extends Cache
{
    protected $cache;

    public function __construct()
    {
        $this->cache = new Redis();
        $this->cache->connect('localhost', 6379);
    }

    public function set($key, $value) {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        return $this->cache->set($key, $value);
    }

    public function get($key)
    {
        $value = $this->cache->get($key);
        $decodeValue = json_decode($value, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $value = $decodeValue;
        }

        return $value;
    }

    public function delete($key)
    {
        return $this->cache->delete($key);
    }

    public function getSessionKey($uniqueValue)
    {
        return CACHE_PREFIX . md5($uniqueValue);
    }
}
