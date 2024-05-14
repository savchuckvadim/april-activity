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
    public function toArray( $request): array
    {
        // return parent::toArray($request);
        $resultfields = [];
        foreach ($this->fields as $field) {
            $field = new BitrixFieldResource ($field);
           array_push($resultfields, $field);
        }
        return [
            'id' => $this->id,
            'type' => $this->type,
            'group' => $this->group,
            'name' => $this->name,
            'title' => $this->title,
            'bitrixId' => $this->bitrixId,
            'portal_id' => $this->portal_id,
            'bitrixfields' => $resultfields,

        ];
    }
}
