<?php

namespace App\Http\Controllers;

use App\Http\Resources\CounterResource;
use App\Models\Counter;
use App\Models\Rq;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $rq = Rq::find($request->input('rq_id'));

        if (!$rq) {
            return APIController::getError('Rq not found', []);
        }

        $counter = new Counter([
            'name' => $request->input('name'),
            'title' => $request->input('title'),
            // Другие поля...
        ]);
        $counter->save();

        // Преобразование строк 'false', 'null', '0' в булево false
        $day = filter_var($request->input('day'), FILTER_VALIDATE_BOOLEAN);
        $month = filter_var($request->input('month'), FILTER_VALIDATE_BOOLEAN);
        $year = filter_var($request->input('year'), FILTER_VALIDATE_BOOLEAN);

        $relationData = [
            'value' => (int) $request->input('value', 0), // Приведение к integer, с предоставлением значения по умолчанию
            'type' => $request->input('type'),
            'prefix' => $request->input('prefix'),
            'postfix' => $request->input('postfix'),
            'day' => $day,
            'year' => $year,
            'month' => $month,
            'count' => (int) $request->input('count', 0), // Аналогично
            'size' => (int) $request->input('size', 1), // Аналогично
        ];

        $rq->counters()->attach($counter->id, $relationData);
        $counter = $counter->load('rqs');
        return APIController::getSuccess(['counter' => new CounterResource($counter)]);
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


    public static function getAll($rqId)
    {

        $rq = Rq::with(['counters'])->find($rqId);

        if (!$rq) {
            return APIController::getError('Rq not found', []);
        }

        $data = [
            'counters' => CounterResource::collection($rq->counters)
        ];

        return APIController::getSuccess($data);
    }


    public static function delete($counterId)
    {
        try {
            $counter = Counter::findOrFail($counterId);

            // Отсоединяем все связанные шаблоны (Rq)
            $counter->rqs()->detach();

            // Удаление самого счетчика
            $counter->delete();

            return APIController::getSuccess(['message' => 'Counter and its relations successfully deleted.']);
        } catch (\Exception $e) {
            return APIController::getError('Failed to delete counter.', []);
        }
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
