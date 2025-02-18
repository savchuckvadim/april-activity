<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfferZakupkiSettings extends Model
{
    use HasFactory;
    protected $fillable = [
        'portal_id',
        'bxUserId',
        'offer_template_id',
        'domain',
        'name',
        'provider1_id',
        'provider1_name',
        'provider1_shortname',
        'provider1_address',
        'provider1_phone',
        'provider1_email',
        'provider1_letter_text',
        'provider1_inn',
        'provider1_director',
        'provider1_position',
        'provider1_logo',
        'provider1_stamp',
        'provider1_signature',
        'provider1_price_coefficient',
        'provider2_id',
        'provider2_name',
        'provider2_shortname',
        'provider2_address',
        'provider2_phone',
        'provider2_email',
        'provider2_letter_text',
        'provider2_inn',
        'provider2_director',
        'provider2_position',
        'provider2_logo',
        'provider2_stamp',
        'provider2_signature',
        'provider2_price_coefficient',
        'is_default',
        'is_current',
        'is_one_document',
        'provider1_price_settings',
        'provider2_price_settings',
    ];
}
