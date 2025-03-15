<?php

namespace App\Telegram\Commands\Spirit\Enums;

enum CoreList: string
{
    case CORE_95  = 'core_95';
    case CORE_99  = 'core_99';
    case CORE_100 = 'core_100';
    case CORE_110 = 'core_110';
    case CORE_120 = 'core_120';

    public function label(): string {
        return match ($this) {
            self::CORE_95  => 'Ядро души эльфа [95]',
            self::CORE_99  => 'Ядро души эльфа [99]',
            self::CORE_100 => 'Ядро души эльфа [100]',
            self::CORE_110 => 'Ядро души эльфа [110]',
            self::CORE_120 => 'Ядро души эльфа [120]',
        };
    }

    public function damage(): array {
        return match ($this) {
            self::CORE_95  => ['min' => 177, 'max' => 194],
            self::CORE_99  => ['min' => 206, 'max' => 222],
            self::CORE_100 => ['min' => 241, 'max' => 255],
            self::CORE_110 => ['min' => 331, 'max' => 350],
            self::CORE_120 => ['min' => 377, 'max' => 399],
        };
    }
}
