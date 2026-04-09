<?php

namespace App\Telegram\Cache;

use Redis;
use App\Telegram\Database\MySql;

class DbCache extends Cache
{
    protected $mysql;

    public function __construct(MySql $mysql)
    {
        $this->mysql = $mysql;
    }

    public function set($key, $value) {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        $conn = $this->mysql->getConnection();
        $stmt = $conn->prepare("
            INSERT INTO telegram_bot (`json`, `user`)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE `json` = VALUES(`json`), `active` = 1
        ");
        $stmt->bind_param("ss", $value, $key);
        $stmt->execute();

        $stmt->close();
    }

    public function get($key)
    {
        $value = '';
        $conn = $this->mysql->getConnection();

        $stmt = $conn->prepare("SELECT `json` FROM telegram_bot WHERE `active` = 1 AND `user` = ?");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->bind_result($json);
        if ($stmt->fetch()) {
            $decodeValue = json_decode($json, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decodeValue;
            }
        }
        $stmt->close();

        return $value;
    }

    public function delete($key)
    {
        $conn = $this->mysql->getConnection();

        $stmt = $conn->prepare("
            UPDATE telegram_bot
            SET `active` = 0
            WHERE `user` = ?
        ");
        $stmt->bind_param("s", $key);
        $stmt->execute();
        $stmt->close();
    }

    public function getSessionKey($uniqueValue)
    {
        return CACHE_PREFIX . md5($uniqueValue);
    }
}
