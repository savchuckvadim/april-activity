<?php

namespace App\Http\Resources;

use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // aprilName: "Internet"
        // bitrixName: "Internet"
        // discount: 1
        // itemId: 6777
        // measureCode: 6
        // measureFullName: "Месяц"
        // measureId: 1
        // measureName: "мес."
        // measureNumber: 0
        // number: 0
        // order: 0
        // prepayment: 1
        // shortName: "internet"


        $fieldItem = $this->bitrixfieldItem;
        $field = Bitrixfield::find($fieldItem['bitrixfield_id']);
        return [
            'id' => $this->id,
            'contract' => $this->contract,
            'portalMeasure' => $this->portalMeasure,
            'code' => $this->contract->code,
            'shortName' => $this->contract->code,
            'number' => $this->contract->number,
            // 'fieldItem' => $fieldItem->bitrixId,
            // 'field' => $field,
            'aprilName' => $this->contract->title,
            'bitrixName' => $this->contract->title,
            'discount' => (int)$this->contract->discount,
            'itemId' =>  $fieldItem->bitrixId,
            'prepayment' => (int)$this->contract->prepayment,
            'order' => $this->contract->order,


        ];
    }
}
