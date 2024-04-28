<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BitrixFieldResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'code' => $this->code,
            'name' => $this->name,
            'title' => $this->title,
            'bitrixId' => $this->bitrixId,
            'bitrixCamelId' => $this->bitrixCamelId,
            'entity_id' => $this->entity_id,
            'bitrixfielditems' => $this->items,
            'entity' => $this->entity,
        ];
    }
}
