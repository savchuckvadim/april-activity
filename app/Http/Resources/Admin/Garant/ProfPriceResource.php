<?php

namespace App\Http\Resources\Admin\Garant;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfPriceResource extends JsonResource
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
            'complect_id' => $this->complect_id,
            'garant_package_id' => $this->garant_package_id,
            'supply_id' => $this->supply_id,
            'region_type' => $this->region_type,
            'supply_type' => $this->supply_type,
            'value' => $this->value,
            'discount' => $this->discount,
            'complect' => $this->complect,
            'garantPackage' => $this->garantPackage,
            'supply' => $this->supply,
        ];
    }
}
