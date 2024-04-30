<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BtxCategoryResource extends JsonResource
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
            'group' => $this->group,
            'name' => $this->name,
            'title' => $this->title,
            'bitrixId' => $this->bitrixId,
            'bitrixCamelId' => $this->bitrixCamelId,
            'code' => $this->code,

            'isActive' => $this->isActive,

            'entity_id' => $this->entity_id,
            'entity_type' => $this->entity_type,
            'parent_type' => $this->parent_type,
            'stages' => $this->stages,
     

        ];
    }
}
