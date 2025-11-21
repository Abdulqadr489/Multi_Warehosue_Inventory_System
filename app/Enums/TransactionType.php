<?php

namespace App\Enums;

enum TransactionType : string
{
    case IN = 'IN';
    case OUT = 'OUT';


    public static function values(): array
    {
        return [
            self::IN,
            self::OUT,
        ];
    }
}
