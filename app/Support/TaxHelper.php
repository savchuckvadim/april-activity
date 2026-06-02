<?php

namespace App\Support;

use App\Enums\ContractLType;

class TaxHelper
{
    /**
     * Mirrors the JS getWithTax(provider, contractType):
     * (provider?.withTax && contractType !== CONTRACT_LTYPE.LIC) || false
     *
     * @param array|null $provider provider/agent payload that may contain a `withTax` flag
     * @param string|null $contractType one of ContractLType::* (or null when not applicable)
     */
    public static function getWithTax(?array $provider, ?string $contractType): bool
    {
        return !empty($provider['withTax']) && $contractType !== ContractLType::LIC;
    }
}
