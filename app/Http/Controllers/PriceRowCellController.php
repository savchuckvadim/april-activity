<?php

namespace App\Http\Controllers;

use App\Models\PriceRowCell;
use Illuminate\Http\Request;

class PriceRowCellController extends Controller
{
    public static function setCells($pricerowcells)
    {
        $result = [];

        foreach ($pricerowcells as $cell) {

            $searchingCell = PriceRowCell::updateOrCreate(
                ['number' => $cell['number']], // Условие для поиска
                $cell // Данные для обновления или создания
            );
            $searchingCell->save();
            $result[] = $searchingCell;
        }

        return response([
            'resultCode' => 0,
            'pricerowcells' => $result
        ]);

    }
}
