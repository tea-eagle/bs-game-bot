<?php

namespace App\Telegram\Commands\Forge\Enums;

enum ForgeList: int
{
    case LEVEL_0   = 0;
    case LEVEL_1   = 1;
    case LEVEL_2   = 2;
    case LEVEL_3   = 3;
    case LEVEL_4   = 4;
    case LEVEL_5   = 5;
    case LEVEL_6   = 6;
    case LEVEL_7   = 7;
    case LEVEL_8   = 8;
    case LEVEL_9   = 9;
    case LEVEL_10  = 10;
    case LEVEL_11  = 11;
    case LEVEL_12  = 12;
    case LEVEL_13  = 13;
    case LEVEL_14  = 14;
    case LEVEL_15  = 15;
    case LEVEL_16  = 16;
    case LEVEL_17  = 17;
    case LEVEL_18  = 18;
    case LEVEL_19  = 19;
    case LEVEL_20  = 20;
    case LEVEL_21  = 21;
    case LEVEL_22  = 22;
    case LEVEL_23  = 23;
    case LEVEL_24  = 24;
    case LEVEL_25  = 25;
    // case LEVEL_26  = 26;
    // case LEVEL_27  = 27;
    // case LEVEL_28  = 28;
    // case LEVEL_29  = 29;
    // case LEVEL_30  = 30;
    // case LEVEL_31  = 31;
    // case LEVEL_32  = 32;
    // case LEVEL_33  = 33;
    // case LEVEL_34  = 34;
    // case LEVEL_35  = 35;
    // case LEVEL_36  = 36;
    // case LEVEL_37  = 37;
    // case LEVEL_38  = 38;
    // case LEVEL_39  = 39;
    // case LEVEL_40  = 40;

    public function data(): array {
        return match ($this) {
            self::LEVEL_0   => ['cost' => 75,    'price' => 10],
            self::LEVEL_1   => ['cost' => 210,   'price' => 12],
            self::LEVEL_2   => ['cost' => 405,   'price' => 14],
            self::LEVEL_3   => ['cost' => 660,   'price' => 17],
            self::LEVEL_4   => ['cost' => 975,   'price' => 20],
            self::LEVEL_5   => ['cost' => 1350,  'price' => 23],
            self::LEVEL_6   => ['cost' => 1785,  'price' => 26],
            self::LEVEL_7   => ['cost' => 2280,  'price' => 30],
            self::LEVEL_8   => ['cost' => 2835,  'price' => 30],
            self::LEVEL_9   => ['cost' => 3450,  'price' => 30],
            self::LEVEL_10  => ['cost' => 3950,  'price' => 31],
            self::LEVEL_11  => ['cost' => 4860,  'price' => 32.1],
            self::LEVEL_12  => ['cost' => 5655,  'price' => 33.3],
            self::LEVEL_13  => ['cost' => 6510,  'price' => 34.6],
            self::LEVEL_14  => ['cost' => 7425,  'price' => 36],
            self::LEVEL_15  => ['cost' => 8400,  'price' => 37.5],
            self::LEVEL_16  => ['cost' => 9435,  'price' => 39.1],
            self::LEVEL_17  => ['cost' => 10530, 'price' => 40.8],
            self::LEVEL_18  => ['cost' => 11685, 'price' => 42.6],
            self::LEVEL_19  => ['cost' => 12900, 'price' => 44.5],
            self::LEVEL_20  => ['cost' => 14175, 'price' => 46.5],
            self::LEVEL_21  => ['cost' => 15510, 'price' => 48.6],
            self::LEVEL_22  => ['cost' => 16905, 'price' => 50.8],
            self::LEVEL_23  => ['cost' => 18360, 'price' => 53.1],
            self::LEVEL_24  => ['cost' => 19875, 'price' => 55.5],
            self::LEVEL_25  => ['cost' => 21450, 'price' => 58],
            self::LEVEL_26  => ['cost' => 0,     'price' => 0],
            self::LEVEL_27  => ['cost' => 24780, 'price' => 0],
            self::LEVEL_28  => ['cost' => 26535, 'price' => 0],
            self::LEVEL_29  => ['cost' => 28350, 'price' => 69],
            self::LEVEL_30  => ['cost' => 28350, 'price' => 69],
            self::LEVEL_31  => ['cost' => 30225, 'price' => 72],
            self::LEVEL_32  => ['cost' => 32160, 'price' => 75.01],
            self::LEVEL_33  => ['cost' => 34155, 'price' => 78.3],
            self::LEVEL_34  => ['cost' => 36210, 'price' => 81.6],
            self::LEVEL_35  => ['cost' => 38325, 'price' => 85],
            self::LEVEL_36  => ['cost' => 40500, 'price' => 88.5],
            self::LEVEL_37  => ['cost' => 0,     'price' => 0],
            self::LEVEL_38  => ['cost' => 0,     'price' => 0],
            self::LEVEL_39  => ['cost' => 0,     'price' => 0],
            self::LEVEL_40  => ['cost' => 0,     'price' => 0],
        };
    }
}
