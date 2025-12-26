<?php

namespace App\Telegram\Commands\Forge\Enums;

enum ItemType: string
{
    case Weapon     = 'Weapon';
    case Helmet     = 'Helmet';
    case Chestplate = 'Chestplate';
    case Boots      = 'Boots';
    case Cloak      = 'Cloak';
    case Gloves     = 'Gloves';
    case Trousers   = 'Trousers';
    case Belt       = 'Belt';
    case Necklace   = 'Necklace';
    case Ring       = 'Ring';

    public function cost(): int
    {
        return match ($this) {
            self::Weapon     => 216,
            self::Helmet     => 103,
            self::Chestplate => 107,
            self::Boots      => 101,
            self::Cloak      => 108,
            self::Gloves     => 105,
            self::Trousers   => 102,
            self::Belt       => 112,
            self::Necklace   => 109,
            self::Ring       => 110,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Weapon     => 'Оружие',
            self::Helmet     => 'Шлем',
            self::Chestplate => 'Нагрудник',
            self::Boots      => 'Ботинки',
            self::Cloak      => 'Плащ',
            self::Gloves     => 'Перчатки',
            self::Trousers   => 'Штаны',
            self::Belt       => 'Пояс',
            self::Necklace   => 'Ожерелье',
            self::Ring       => 'Кольцо',
        };
    }
}
