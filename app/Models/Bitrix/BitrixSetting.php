<?php

namespace App\Models\Bitrix;

use App\Enums\BitrixSettingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitrixSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'settingable_id',
        'settingable_type',
        'type',
        'code',
        'status',
        'title',
        'description',
        'value',
    ];

    public function settingable()
    {
        return $this->morphTo();
    }

    // Автоматическое преобразование значения
    public function getValueAttribute($value)
    {
        return match ($this->type) {
            BitrixSettingType::CHECKBOX => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            BitrixSettingType::NUMBER   => is_numeric($value) ? $value + 0 : null,
            BitrixSettingType::JSON     => json_decode($value, true),
            default                     => $value,
        };
    }

    public function setValueAttribute($value)
    {
        $this->attributes['value'] = match ($this->type) {
            BitrixSettingType::CHECKBOX => $value ? '1' : '0',
            BitrixSettingType::NUMBER   => is_numeric($value) ? (string) $value : null,
            BitrixSettingType::JSON     => json_encode($value),
            default                     => $value,
        };
    }
}
