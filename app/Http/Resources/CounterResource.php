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
            'value' => $this->pivot->value,
            'type' => $this->pivot->type,
            'prefix' => $this->pivot->prefix,
            'postfix' => $this->pivot->postfix,
            'day' => $this->pivot->day,
            'year' => $this->pivot->year,
            'month' => $this->pivot->month,
            'count' => $this->pivot->count,
            'size' => $this->pivot->size,
            // Добавьте другие поля, которые вам нужны
        ];
    }
}
