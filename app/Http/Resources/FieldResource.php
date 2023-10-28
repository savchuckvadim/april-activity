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
        return [
            'id' => $this->id,
            'number' => $this->number,
            // Добавьте другие поля модели Field, которые вы хотите включить
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
