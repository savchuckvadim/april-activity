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
        $resultfields = [];
        foreach ($this->items as $item) {
            // $field = new BitrixFieldResource ($field);
           array_push($resultfields, $item);
        }
        return [
            'id' => $this->id,
            'type' => $this->type,
            'code' => $this->code,
            'name' => $this->name,
            'title' => $this->title,
            'bitrixId' => $this->bitrixId,
            'bitrixCamelId' => $this->bitrixCamelId,
            'entity_id' => $this->entity_id,
            
            'parent_type' => $this->parent_type,
            'bitrixfielditems' => $this->items(),
            'items' => $resultfields,
        ];
    }
}
