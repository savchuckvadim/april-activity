<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmartResource extends JsonResource
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
            'entityTypeId' => $this->entityTypeId,
            'forStageId' => $this->forStageId,
            'crmId' => $this->crmId,
            'forFilterId' => $this->forFilterId,
            'categories' => $this->categories,
            
        ];
    }
}
