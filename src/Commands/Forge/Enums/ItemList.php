<?php

namespace App\Telegram\Commands\Forge\Enums;

enum ItemList: string
{
    case ORANGE = 'orange_item';
    case PURPLE = 'purple_item';
    case PINK   = 'pink_item';

    public function label(): string {
        return match ($this) {
            self::ORANGE => '🟠 Рыжие предметы',
            self::PURPLE => '🟣 Фиолетовые предметы',
            self::PINK   => '💖Розовые предметы',
        };
    }

    public function cost(): int {
        return match ($this) {
            self::ORANGE => 140,
            self::PURPLE => 300,
            self::PINK   => 1000,
        };
    }
}
