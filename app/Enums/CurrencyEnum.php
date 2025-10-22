<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum CurrencyEnum: string
{
    use EnumTrait;

    case USD = 'usd';
    case KGS = 'kgs';
    case RUB = 'rub';

    public static function langKeys() : array
    {
        return ['currency'];
    }

}