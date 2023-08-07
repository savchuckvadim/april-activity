<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BitrixController extends Controller
{
    public static function hooktest() {
        return response(['hellou' => 'world']);
    }
}
