<?php

namespace App\Enums;

class ContractLType
{
    public const SERVICE = 'service';
    public const LIC     = 'lic';
    public const ABON    = 'abon';
    public const KEY     = 'key';

    public static function all(): array
    {
        return [
            self::SERVICE,
            self::LIC,
            self::ABON,
            self::KEY,
        ];
    }
}
