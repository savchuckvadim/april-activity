<?php

namespace App\Http\Controllers;

use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public static function setTemplates($templates)
    {
        $result = [];

        foreach ($templates as $template) {

            $searchingCell = Template::updateOrCreate(
                ['number' => $template['number']], // Условие для поиска
                $template // Данные для обновления или создания
            );
            $searchingCell->save();
            $result[] = $searchingCell;
        }

//TODO если ему не принадлежат никакие TFields - перебрать все isGeneral Fields сделать из них TFields templateId -> template->id

        return response([
            'resultCode' => 0,
            'pricerowcells' => $result
        ]);

    }
}
