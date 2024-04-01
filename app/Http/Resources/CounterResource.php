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
        $rq = $this->rqs->first();

        return [
            'id' => $this->id,
            'value' => $rq ? $rq->pivot->value : null,
            'type' => $rq ? $rq->pivot->type : null,
            'prefix' => $rq ? $rq->pivot->prefix : null,
            'postfix' => $rq ? $rq->pivot->postfix : null,
            'day' => $rq ? $rq->pivot->day : null,
            'year' => $rq ? $rq->pivot->year : null,
            'month' => $rq ? $rq->pivot->month : null,
            'count' => $rq ? $rq->pivot->count : null,
            'size' => $rq ? $rq->pivot->size : null,
        ];
    }
}
