<?php

namespace App\Models\Konstructor;

use App\Models\Konstructor\OfferTemplate;
use App\Models\Portal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class UserSelectedTemplate extends Model
{
    use HasFactory;

    protected $table = 'user_selected_templates';

    protected $fillable = [
        'bitrix_user_id',
        'portal_id',
        'offer_template_id',
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
        'sale_text_5',
    ];

    /**
     * 🔹 Связь с `OfferTemplate`
     */
    public function template()
    {
        return $this->belongsTo(OfferTemplate::class, 'offer_template_id');
    }

    /**
     * 🔹 Связь с `Portal`
     */
    public function portal()
    {
        return $this->belongsTo(Portal::class, 'portal_id');
    }

    /**
     * 🔹 Получить текущий шаблон пользователя
     */
    public static function getCurrentTemplate(int $bitrixUserId, int $portalId)
    {
        // 1. Найти текущий шаблон пользователя (`is_current = true`)
        $currentTemplate = self::where('bitrix_user_id', $bitrixUserId)
            ->where('portal_id', $portalId)
            ->where('is_current', true)
            ->with('template')
            ->first();

        if ($currentTemplate) {
            return $currentTemplate->template;
        }

        // 2. Найти дефолтный шаблон пользователя (`is_default = true` в user_selected_templates)
        $defaultUserTemplate = self::where('bitrix_user_id', $bitrixUserId)
            ->where('portal_id', $portalId)
            ->whereHas('template', fn($query) => $query->where('is_default', true))
            ->with('template')
            ->first();

        if ($defaultUserTemplate) {
            return $defaultUserTemplate->template;
        }

        // 3. Найти дефолтный шаблон портала (`is_default = true` в offer_template_portal)
        $defaultPortalTemplate = OfferTemplate::whereHas('portals', function ($query) use ($portalId) {
                $query->where('portal_id', $portalId)->where('is_default', true);
            })
            ->first();

        if ($defaultPortalTemplate) {
            return $defaultPortalTemplate;
        }

        // 4. Вернуть глобальный шаблон по умолчанию (`is_default = true` в offer_templates)
        return OfferTemplate::getGlobalDefault();
    }
}
