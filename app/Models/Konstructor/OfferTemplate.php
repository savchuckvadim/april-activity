<?php

namespace App\Models\Konstructor;

use App\Models\Konstructor\UserSelectedTemplate;
use App\Models\Portal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferTemplate extends Model
{
    use HasFactory;

    // Разрешенные для массового заполнения поля
    protected $fillable = [
        'name',
        'visibility',
        'is_default',
        'file_path',
        'demo_path',
        'type',
        'rules',
        'price_settings',
        'infoblock_settings',
        'letter_text',
        'sale_text_1',
        'sale_text_2',
        'sale_text_3',
        'sale_text_4',
        'sale_text_5',
        'field_codes',
        'style',
        'color',
        'code',
        'tags',
        'is_active',
        'counter',
    ];

    /**
     * Отношение многие ко многим с Portal через offer_template_portal
     */
    public function portals()
    {
        return $this->belongsToMany(Portal::class, 'offer_template_portal')
            ->withPivot('is_default', 'is_active')
            ->withTimestamps();
    }

    /**
     * Глобальный шаблон по умолчанию
     */
    public function users()
    {
        return $this->belongsToMany(OfferTemplate::class, 'user_selected_templates', 'offer_template_id', 'bitrix_user_id')
            ->withPivot([
                'portal_id',
                'is_current',
                'is_favorite',
                'is_active',
                'price_settings',
                'infoblock_settings',
                'letter_text',
                'sale_text_1',
                'sale_text_2',
                'sale_text_3',
                'sale_text_4',
                'sale_text_5'
            ])
            ->withTimestamps();
    }

    /**
     * 🔹 Отношение один ко многим к `user_selected_templates`
     */
    public function userSelections()
    {
        return $this->hasMany(UserSelectedTemplate::class, 'offer_template_id');
    }

    /**
     * 🔹 Глобальный шаблон по умолчанию
     */
    public static function getGlobalDefault()
    {
        return self::where('is_default', true)->first();
    }

    /**
     * 🔹 Получить шаблон по умолчанию для портала
     */
    public static function getDefaultForPortal($portalId)
    {
        return self::whereHas('portals', function ($query) use ($portalId) {
            $query->where('portal_id', $portalId)->where('is_default', true);
        })->first() ?? self::getGlobalDefault();
    }

    /**
     * 🔹 Получить текущий шаблон пользователя для портала
     */
    public static function getCurrentForUser(int $bitrixUserId, int $portalId)
    {
        return self::whereHas('userSelections', function ($query) use ($bitrixUserId, $portalId) {
            $query->where('bitrix_user_id', $bitrixUserId)
                ->where('portal_id', $portalId)
                ->where('is_current', true);
        })->first() ?? self::getDefaultForPortal($portalId);
    }
}
