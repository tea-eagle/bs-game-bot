<?php

namespace App\Telegram\Enums;

use App\Telegram\Enums\CalculatorDirection;

enum SpiritCalculatorAction: string
{
    case CORE      = 'choose_core';
    case RESULT    = 'calculate_result';
    case PREV_PAGE = 'prev_page';
    case NEXT_PAGE = 'next_page';

    public static function fromCallback(?string $callbackData): ?self
    {
        return match (true) {
            // зачем я так сделал?
            str_starts_with($callbackData, CalculatorDirection::SPIRIT->value) => CalculatorDirection::SPIRIT,
            // выбор духа
            str_starts_with($callbackData, 'spirit_') => self::CORE,
            // выбор ядра
            str_starts_with($callbackData, 'core_') => self::RESULT,
            // пагинация
            $callbackData === self::PREV_PAGE->value => self::PREV_PAGE,
            $callbackData === self::NEXT_PAGE->value => self::NEXT_PAGE,
            default => null
        };
    }
}
