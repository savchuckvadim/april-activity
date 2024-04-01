<?php

namespace App\Http\Controllers;

use App\Http\Resources\CounterResource;
use App\Models\Counter;
use App\Models\Rq;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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


    public static function getCount($rqId, $type)
    {

        // Попытка найти Rq и счетчик определенного типа
        $rq = Rq::with(['counters' => function ($query) use ($type) {
            $query->where('type', $type);
        }])->find($rqId);
        Log::channel('telegram')->info($type);
        Log::channel('telegram')->info($rq);
        // Если Rq найден и имеет связанные счетчики
        if ($rq && $rq->counters->isNotEmpty()) {
            $counter = $rq->counters->first();
            $pivot = $counter->pivot;
            $currentCount = (int)$pivot->count + (int)$pivot->size;

            // Обновляем счётчик в pivot-таблице
            $rq->counters()->updateExistingPivot($counter->id, ['count' => $currentCount]);

            // Формируем и возвращаем номер документа с учетом pivot данных
            return self::formatDocumentNumber($pivot, $currentCount);
        }

        // Логика для возврата номера документа, если Rq или Counter не найдены
        $day = (int)date('d'); // Преобразует "03" в 3
        $month = ltrim(date('m'), '0'); // Удаляет ведущие нули, превращая "09" в "9"
        $randomNumber = mt_rand(1, 99);
        return "{$rqId}{$month}-{$randomNumber}{$day}";
    }

    protected static function formatDocumentNumber($pivot, $currentCount)
    {
        $parts = [$currentCount]; // Начинаем с обновленного значения счетчика

        if ($pivot->prefix) {
            array_unshift($parts, $pivot->prefix); // Добавляем префикс в начало
        }

        if ($pivot->day) {
            $parts[] = date('d');
        }
        if ($pivot->month) {
            $parts[] = date('m');
        }
        if ($pivot->year) {
            $parts[] = date('Y');
        }
        if ($pivot->postfix) {
            $parts[] = $pivot->postfix; // Добавляем постфикс в конец
        }

        return implode('-', $parts);
    }
}
