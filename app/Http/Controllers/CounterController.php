<?php

namespace App\Http\Controllers;

use App\Http\Resources\CounterResource;
use App\Models\Counter;
use App\Models\Rq;
use App\Models\Template;
use Illuminate\Http\Request;

class CounterController extends Controller
{
    public static function getInitial($rqId =  null)
    {

        $initialData = Counter::getForm($rqId);
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
        $type = $request['type'];
        $prefix = $request['prefix'];
        $postfix = $request['postfix'];
        $size = $request['size'];
        $count = $request['count'];
        $day = $request['day'];
        $month = $request['month'];
        $year = $request['year'];
        // $template_id = $request['template_id'];
        $rq_id = $request['rq_id'];
        if (!$day || $day == 'false' || $day == 'null' || $day == '0'  || $day == '') {
            $day = 0;
        } else {
            $day = 1;
        }
        if (!$month || $month == 'false' || $month == 'null' || $month == '0'  || $month == '') {
            $month = 0;
        } else {
            $month = 1;
        }
        if (!$year || $year == 'false' || $year == 'null' || $year == '0' || $year == '') {
            $year = 0;
        } else {
            $year = 1;
        }

        $rq = Rq::find($rq_id);

        if ($rq) {
            // Создание нового Counter
            $counter = new Counter;
            if ($counter) {

                $counter->name = $name;
                $counter->title = $title;
                $counter->save(); // Сохранение Counter в базе данных


                $relationData = [
                    'value' => $value,
                    'type' => $type,
                    'prefix' => $prefix,
                    'postfix' => $postfix,
                    'day' => $day, // или false
                    'year' => $year, // или false
                    'month' => $month, // или false
                    'count' => $count,
                    'size' => $size,
                    // 'template_id' => $template_id,
                    'rq_id' => $rq_id,

                ];
                // Установка связи с Template и добавление данных в сводную таблицу
                $rq->counters()->attach($counter->id, $relationData);
                return APIController::getSuccess(
                    ['counter' => $counter, 'rq' => $rq]
                );
            }
        }

        return APIController::getError(
            'rq or counter was not found',
            ['rq' => $rq]

        );
    }

    public static function get($counterId)
    {
        $counter = Counter::with('rqs')->find($counterId);
      
        $data = [
            'counter' => $counter

        ];

        if (!$counter) {
            // Обработка случая, когда счетчик не найден
            return APIController::getError('Counter not found', $data);
        }
        $counterResource = new CounterResource($counter);
        $data = [
            'counter' => $counterResource

        ];


        return APIController::getSuccess($data);
    }


    public static function getAll()
    {

        $counters = Counter::with(['rqs' => function ($query) {
            $query->select('rqs.id', 'rqs.name'); // Замените 'name' на нужное поле, если это название
        }])->get();


        $data = [
            'counters' => $counters
        ];
        if (!$counters) {
            // Обработка случая, когда счетчик не найден
            return APIController::getError('Counter not found', $data);
        }


        return APIController::getSuccess($data);
    }
    public static function getCount($templateId)
    {

        $template = Template::find($templateId);
        $counter = null;
        $count = 0;
        // if ($template) {
        //     $templateCounters = $template->counters;
        //     if ($templateCounters && count($templateCounters) > 0) {

        //         $counterId =   $templateCounters[0]['id'];
        //         $counter = $templateCounters[0];

        //         if ($counter && isset($counter['pivot'])) {
        //             $counter = $counter['pivot'];
        //             $baseCount = '';
        //             if (isset($counter['count']) && isset($counter['size'])) {
        //                 $size = 1;
        //                 $currentCount = 0;
        //                 if ($counter['size']) {
        //                     $size = $counter['size'];
        //                 }
        //                 if ($counter['count']) {
        //                     $currentCount = $counter['count'] + 1;
        //                 }
        //                 $counter['count'] =  $currentCount;
        //                 $template->counters()->updateExistingPivot($counterId, ['count' => $currentCount]);
        //                 $baseCount = $currentCount + ($currentCount *  $size);
        //             }

        //             if (isset($counter['prefix']) && $counter['prefix']) {
        //                 $count = $counter['prefix'] . '-' . $baseCount;
        //             }

        //             if (isset($counter['day']) && $counter['day']) {
        //                 $day = date('d');
        //                 $count = $count . '-' . $day;
        //             }

        //             if (isset($counter['month']) && $counter['month']) {
        //                 $month = date('m');
        //                 $count = $count . '-' . $month;
        //             }

        //             if (isset($counter['year']) && $counter['year']) {
        //                 $year = date('y');
        //                 $count = $count . '-' . $year;
        //             }
        //         }
        //     }
        // }


        if (!$count) {
            $day = date('d');
            $month = date('m');
            $count  = $templateId . mt_rand(1, 99) . $month . '-' . $day;
        }
        return $count;
    }
}
