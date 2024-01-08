<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FieldResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // $this->except('updated_at');
        // $this->except('created_at');
        return [
            'id' => $this->id,
            'number' => $this->number,
            'items' => $this->items,
            'name'=> $this->name,
            'code'=> $this->code,
            'type'=> $this->type,
            'isGeneral'=> $this->isGeneral,
            'isDefault'=> $this->isDefault,
            'isRequired'=> $this->isRequired,
            'value'=> $this->value,
            'description'=> $this->description,
            'bitixId'=> $this->bitixId,
            'bitrixTemplateId'=> $this->bitrixTemplateId,
            'isActive'=> $this->isActive,
            'isPlural'=> $this->isPlural,


        ];
    }
}
