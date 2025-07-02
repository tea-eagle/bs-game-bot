<?php

namespace App\Telegram\Enums;

enum CalculatorDirection: string
{
    case PATTERN = '/rune';
    case SPIRIT  = '/spirit';
    case FORGE   = '/forge';
}
