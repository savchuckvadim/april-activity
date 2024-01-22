<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Template;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public static function getInitial($templateId)
    {

        $initialData = Counter::getForm($templateId);
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }

    public static function set(Request $request)
    {
        // Предполагая, что у вас уже есть экземпляр Template
        $name = $request['name'];
        $title = $request['title'];
        $value = $request['value'];
        $prefix = $request['prefix'];
        $size = $request['size'];
        $count = $request['count'];
        $day = $request['day'];
        $month = $request['month'];
        $year = $request['year'];
        $template_id = $request['template_id'];


        $template = Template::find($template_id);

        if ($template) {
            // Создание нового Counter
            $counter = new Counter;
            if ($counter) {

                $counter->name = $name;
                $counter->title = $title;
                $counter->save(); // Сохранение Counter в базе данных


                $relationData = [
                    'value' => $value,
                    'prefix' => $prefix,
                    'day' => $day, // или false
                    'year' => $year, // или false
                    'month' => $month, // или false
                    'count' => $count,
                    'size' => $size,
                    // 'template_id' => $template_id,
       
                ];
                // Установка связи с Template и добавление данных в сводную таблицу
                $template->counters()->attach($counter->id, $relationData);
                return APIController::getSuccess(
                    ['counter' => $counter, 'template' => $template]
                );
            }
        }

        return APIController::getError(
            'template or counter was not found',
            ['template' => $template]

        );
    }
}
