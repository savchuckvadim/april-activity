<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DealDocumentOption extends Model
{
    use HasFactory;
    protected $fillable = [
        'domain',
        'portalId',
        'bxUserId',
        'domain',
        'offer_template_id',
        'actionId',

        'dealId',
        'dealDocumentFavoriteId',
        'salePhrase',
        'withStamp',
        'isPriceFirst',
        'withManager',
        'iblocksStyle',
        'describStyle',
        'otherStyle',
        'priceDiscount',
        'priceYear',
        'priceDefault',
        'priceSupply',
        'priceOptions',
        'otherPrice',
        'otherSettings'

    ];

    public static function findOrCreateOptions(array $validatedData, Portal $portal)
    {
        $query = self::query();

        if (!empty($validatedData['dealDocumentFavoriteId'])) {
            // Если передан favoriteId – ищем только по нему
            $query->where('dealDocumentFavoriteId', $validatedData['dealDocumentFavoriteId']);
        } else {
            // Иначе ищем по пользователю и порталу
            $query->where('portalId', $portal->id)
                ->where('bxUserId', $validatedData['bxUserId']);
        }

        $options = $query->first();

        if (!$options) {
            return self::create($validatedData);
        }

        $options->update($validatedData);
        return $options;
    }

    public static function getOptions(array $validatedData, Portal $portal)
    {
        $query = self::query();

        if (!empty($validatedData['dealDocumentFavoriteId'])) {
            // Если передан favoriteId – ищем только по нему
            $query->where('dealDocumentFavoriteId', $validatedData['dealDocumentFavoriteId']);
        } else {
            // Иначе ищем по пользователю и порталу
            $query->where('portalId', $portal->id)
                ->where('bxUserId', $validatedData['bxUserId']);
        }

        return $query->first();
    }

    public function favorites()
    {
        return $this->hasMany(DealDocumentFavorite::class, 'dealDocumentOptionId');
    }
}
