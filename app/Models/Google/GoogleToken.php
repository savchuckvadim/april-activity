<?php

namespace App\Models\Google;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoogleToken extends Model

{
    protected $fillable = ['access_token', 'refresh_token', 'expires_at'];
}
