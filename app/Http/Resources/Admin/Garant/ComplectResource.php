<?php

namespace App\Http\Resources\Admin\Garant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplectResource extends JsonResource
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
            'name' => $this->name,
            'fullName' => $this->fullName,
            'shortName' => $this->shortName,
            'description' => $this->description,
            'code' => $this->code,
            'type' => $this->type,
            'color' => $this->color,
            'weight' => $this->weight,
            'abs' => $this->abs,
            'number' => $this->number,
            'productType' => $this->productType,
            'withABS' => $this->withABS,
            'withConsalting' => $this->withConsalting,
            'withServices' => $this->withServices,
            'withLt' => $this->withLt,
            'isChanging' => $this->isChanging,
            'withDefault' => $this->withDefault,

            'infoblocks' => $this->infoblocks



        ];
    }
}
