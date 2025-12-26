<?php

namespace App\Telegram\Commands\Spirit\Enums;

enum SpiritList: string
{
    case NANDO                      = 'spirit_1';
    case DOLLIS                     = 'spirit_2';
    case TAISI                      = 'spirit_3';
    case KORRADI                    = 'spirit_4';
    case MIKAL                      = 'spirit_5';
    case GOD_LIGTH_MIKAELA          = 'spirit_6';
    case FOREST_MIRTA               = 'spirit_7';
    case GUARDIAN_ABYSS_ASMODAY     = 'spirit_8';
    case ICE_GUARDIAN_ADRIEL        = 'spirit_9';
    case WAR_GRADOZOR               = 'spirit_10';
    case GUARDIAN_HOLIDAY_ILYRA     = 'spirit_11';
    case ETERNITY_HARON             = 'spirit_12';
    case POISON_FLOWER_MATILDA      = 'spirit_13';
    case ICE_QUEEN_ZELDA            = 'spirit_14';
    case BLOODY_COUNTESS_ELIZA      = 'spirit_15';
    case ICE_MAIDEN_ADEL            = 'spirit_16';
    case DESERT_KLEOPATRA           = 'spirit_17';
    case ANGEL_REVENGE_ALESTA       = 'spirit_18';
    case KNIGHT_JUSTICE_ALNAR       = 'spirit_19';
    case DOOMSDAY_EXECUTIONER_RIOKU = 'spirit_20';
    case GHOSTLY_KITANA             = 'spirit_21';
    case FROSTY_SOFIA               = 'spirit_22';
    case LEGENDARY_BIANKA           = 'spirit_23';
    case GUARDIAN_DARK_TOWER_ABIGOR = 'spirit_24';
    case NIGHT_FURY_CATAPLEXY       = 'spirit_25';
    case VENGEFUL_CHARIONNE         = 'spirit_26';
    case WISDOM_MIMIR               = 'spirit_27';
    case JUSTICE_RAIT               = 'spirit_28';
    case GABRIELLA                  = 'spirit_29';
    case ARES                       = 'spirit_30';

    public function label(): string {
        return match ($this) {
            self::NANDO                      => 'Дух Наньдо',
            self::DOLLIS                     => 'Дух Доллис',
            self::TAISI                      => 'Дух Тайси',
            self::KORRADI                    => 'Дух Корради',
            self::MIKAL                      => 'Демон Микал',
            self::GOD_LIGTH_MIKAELA          => 'Дух божественного света Микаэла',
            self::FOREST_MIRTA               => 'Лесной дух Мирта',
            self::GUARDIAN_ABYSS_ASMODAY     => 'Хранитель бездны Асмодей',
            self::ICE_GUARDIAN_ADRIEL        => 'Ледяной страж Адриэль',
            self::WAR_GRADOZOR               => 'Дух войны Градозор',
            self::GUARDIAN_HOLIDAY_ILYRA     => 'Страж праздника Илура',
            self::ETERNITY_HARON             => 'Дух вечности Харон',
            self::POISON_FLOWER_MATILDA      => 'Ядовитый цветок Матильда',
            self::ICE_QUEEN_ZELDA            => 'Морозная царица Зельда',
            self::BLOODY_COUNTESS_ELIZA      => 'Кровавая графиня Элиза',
            self::ICE_MAIDEN_ADEL            => 'Ледяная дева Адель',
            self::DESERT_KLEOPATRA           => 'Пустынный дух Клеопатра',
            self::ANGEL_REVENGE_ALESTA       => 'Ангел мести Алеста',
            self::KNIGHT_JUSTICE_ALNAR       => 'Рыцарь правосудия Алнар',
            self::DOOMSDAY_EXECUTIONER_RIOKU => 'Палач судного дня Риоку',
            self::GHOSTLY_KITANA             => 'Призрачный дух Китана',
            self::FROSTY_SOFIA               => 'Морозный дух София',
            self::LEGENDARY_BIANKA           => 'Легендарная Бьянка',
            self::GUARDIAN_DARK_TOWER_ABIGOR => 'Страж Темной Башни Абигор',
            self::NIGHT_FURY_CATAPLEXY       => 'Ночная фурия Катаплексия',
            self::VENGEFUL_CHARIONNE         => 'Мстительный дух Харионна',
            self::WISDOM_MIMIR               => 'Дух мудрости Мимир',
            self::JUSTICE_RAIT               => 'Дух правосудия Райт',
            self::GABRIELLA                  => 'Святой дух Габриэлла',
            self::ARES                       => 'Свирепый дух Арес',
        };
    }

    public function damage(): int {
        return match ($this) {
            self::NANDO                      => 7833,
            self::DOLLIS                     => 7125,
            self::TAISI                      => 6300,
            self::KORRADI                    => 6455,
            self::MIKAL                      => 12000,
            self::GOD_LIGTH_MIKAELA          => 10500,
            self::FOREST_MIRTA               => 14175,
            self::GUARDIAN_ABYSS_ASMODAY     => 16200,
            self::ICE_GUARDIAN_ADRIEL        => 16200,
            self::WAR_GRADOZOR               => 16200,
            self::GUARDIAN_HOLIDAY_ILYRA     => 18630,
            self::ETERNITY_HARON             => 18630,
            self::POISON_FLOWER_MATILDA      => 18630,
            self::ICE_QUEEN_ZELDA            => 21424,
            self::BLOODY_COUNTESS_ELIZA      => 24637,
            self::ICE_MAIDEN_ADEL            => 28332,
            self::DESERT_KLEOPATRA           => 32581,
            self::ANGEL_REVENGE_ALESTA       => 36490,
            self::KNIGHT_JUSTICE_ALNAR       => 40399,
            self::DOOMSDAY_EXECUTIONER_RIOKU => 44308,
            self::GHOSTLY_KITANA             => 48217,
            self::FROSTY_SOFIA               => 52126,
            self::LEGENDARY_BIANKA           => 56035,
            self::GUARDIAN_DARK_TOWER_ABIGOR => 59944,
            self::NIGHT_FURY_CATAPLEXY       => 63853,
            self::VENGEFUL_CHARIONNE         => 67762,
            self::WISDOM_MIMIR               => 71671,
            self::JUSTICE_RAIT               => 75580,
            self::GABRIELLA                  => 79489,
            self::ARES                       => 83398,
        };
    }

    public function absorption(): int {
        return match ($this) {
            self::NANDO                      => 150,
            self::DOLLIS                     => 150,
            self::TAISI                      => 150,
            self::KORRADI                    => 150,
            self::MIKAL                      => 186,
            self::GOD_LIGTH_MIKAELA          => 186,
            self::FOREST_MIRTA               => 219,
            self::GUARDIAN_ABYSS_ASMODAY     => 219,
            self::ICE_GUARDIAN_ADRIEL        => 219,
            self::WAR_GRADOZOR               => 219,
            self::GUARDIAN_HOLIDAY_ILYRA     => 219,
            self::ETERNITY_HARON             => 219,
            self::POISON_FLOWER_MATILDA      => 219,
            self::ICE_QUEEN_ZELDA            => 219,
            self::BLOODY_COUNTESS_ELIZA      => 219,
            self::ICE_MAIDEN_ADEL            => 219,
            self::DESERT_KLEOPATRA           => 219,
            self::ANGEL_REVENGE_ALESTA       => 219,
            self::KNIGHT_JUSTICE_ALNAR       => 219,
            self::DOOMSDAY_EXECUTIONER_RIOKU => 219,
            self::GHOSTLY_KITANA             => 219,
            self::FROSTY_SOFIA               => 219,
            self::LEGENDARY_BIANKA           => 219,
            self::GUARDIAN_DARK_TOWER_ABIGOR => 219,
            self::NIGHT_FURY_CATAPLEXY       => 219,
            self::VENGEFUL_CHARIONNE         => 219,
            self::WISDOM_MIMIR               => 219,
            self::JUSTICE_RAIT               => 219,
            self::GABRIELLA                  => 219,
            self::ARES                       => 219,
        };
    }
}
