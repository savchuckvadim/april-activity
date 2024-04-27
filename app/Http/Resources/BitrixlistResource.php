<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BitrixlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'type' => $this->type,
            'group' => $this->group,
            'name' => $this->name,
            'title' => $this->title,
            'bitrixId' => $this->bitrixId,
            'portal_id' => $this->portal_id,
            'bitrixlistfields' => $this->fields,

        ];
    }
}
