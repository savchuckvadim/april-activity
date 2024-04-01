<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CounterResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Предполагаем, что 'rqs' - это название отношения многие ко многим
        return [
            'id' => $this->id,
            'rqs' => $this->whenLoaded('rqs', function () {
                // Возвращаем данные из pivot, если связь 'rqs' загружена
                return $this->rqs->map(function ($rq) {
                    return [
                        'id' => $rq->id,
                        'name' => $rq->name,
                        // Пример возвращения данных pivot
                        'pivot' => [
                            'value' => $rq->pivot->value,
                            'type' => $rq->pivot->type,
                            'prefix' => $rq->pivot->prefix,
                            'postfix' => $rq->pivot->postfix,
                            'day' => $rq->pivot->day,
                            'year' => $rq->pivot->year,
                            'month' => $rq->pivot->month,
                            'count' => $rq->pivot->count,
                            'size' => $rq->pivot->size,
                        ],
                    ];
                });
            }),
        ];
    }
}
