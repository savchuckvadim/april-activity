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
        $resultfields = [];
        foreach ($this->fields as $field) {
            $field = new BitrixFieldResource ($field);
           array_push($resultfields, $field);
        }
        return [
            'id' => $this->id,
            'portal_id' => $this->portal_id,
            'code' => $this->code,
            'name' => $this->name,
            'title' => $this->title,
            'bitrixfields' => $this->resultfields,
            
        ];
    }
}
