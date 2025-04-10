<?php

namespace App\Enums;

class BitrixSettingType
{
    public const TEXT = 'text';
    public const CHECKBOX = 'checkbox';
    public const NUMBER = 'number';
    public const JSON = 'json';
    public const TOKEN = 'token';
    public const SELECT = 'select';
    public const URL = 'url';

    public static function all(): array
    {
        return [
            self::TEXT,
            self::CHECKBOX,
            self::NUMBER,
            self::JSON,
            self::TOKEN,
            self::SELECT,
            self::URL,
        ];
    }
}
