<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BtxCompanyResource extends JsonResource
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
            'portal_id' => $this->portal_id,
            'code' => $this->code,
            'name' => $this->name,
            'title' => $this->title,
            'bitrixfields' => $this->fields,
            
        ];
    }
}
