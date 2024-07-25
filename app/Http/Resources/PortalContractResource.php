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
        $measure = $this->portalMeasure;
        $contract = $this->contract;
        $field = Bitrixfield::find($fieldItem['bitrixfield_id']);
        return [
            'id' => $this->id,
            'contract' => $contract,

            'code' => $contract->code,
            'shortName' => $contract->code,
            'number' => $contract->number,
            // 'fieldItem' => $fieldItem->bitrixId,
            // 'field' => $field,
            'aprilName' => $contract->title,
            'bitrixName' => $contract->title,
            'discount' => (int)$contract->discount,
            'itemId' =>  $fieldItem->bitrixId,
            'prepayment' => (int)$contract->prepayment,
            'order' => (int)$contract->order || (int)$this->order,

            'portalMeasure' => $measure,
            'measureCode' =>  $measure->measure->code,
            'measureFullName' =>  $measure->fullName || $measure->measure->fullName,
            'measureId' =>  (int)$measure->bitrixId,
            'measureName' =>  $measure->shortName || $measure->measure->shortName,
            'measureNumber' => $measure->id,


        ];
    }
}
