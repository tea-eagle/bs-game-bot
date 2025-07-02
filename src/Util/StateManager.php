<?php

namespace App\Telegram\Util;

use App\Telegram\Cache\RedisCache;

class StateManager
{
    protected RedisCache $cache;
    protected string $prefix = 'user_state:';

    public function __construct(RedisCache $cache)
    {
        $this->cache = $cache;
    }

    public function getState(int $chatId): array
    {
        $data = $this->cache->get($this->getKey($chatId));
        return $data ? $data : [];
    }

    public function setState(int $chatId, array $newData): void
    {
        $currentData = $this->getState($chatId);
        $mergedData = array_merge($currentData, $newData);
        $this->cache->set($this->getKey($chatId), $mergedData);
    }

    public function clearState(int $chatId): void
    {
        $this->cache->delete([$this->getKey($chatId)]);
    }

    public function addToState($chatId, $key, $value)
    {
        $state = $this->getState($chatId);
        $state[$key] = $value;
        $this->setState($chatId, $state);
    }

    public function removeFromState($chatId, $key)
    {
        $state = $this->getState($chatId);
        unset($state[$key]);
        $this->setState($chatId, $state);
    }

    public function getKey($chatId)
    {
        return $this->prefix . $chatId;
    }
}
