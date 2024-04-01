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
        return [
            'id' => $this->id,
            'value' => $this->pivot ? $this->pivot->value : null,
            'type' => $this->pivot ? $this->pivot->type : null,
            'prefix' => $this->pivot ? $this->pivot->prefix : null,
            'postfix' => $this->pivot ? $this->pivot->postfix : null,
            'day' => $this->pivot ? $this->pivot->day : null,
            'year' => $this->pivot ? $this->pivot->year : null,
            'month' => $this->pivot ? $this->pivot->month : null,
            'count' => $this->pivot ? $this->pivot->count : null,
            'size' => $this->pivot ? $this->pivot->size : null,
            // Добавьте другие поля, которые вам нужны
        ];
    }
}
