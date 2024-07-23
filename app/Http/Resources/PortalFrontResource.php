<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalFrontResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    
    {
        $resultsmarts = [];
        foreach ($this->smarts as $smrt) {
            $smart = new SmartResource($smrt);
           array_push($resultsmarts, $smart);
        }

        $bitrixLists = [];
        foreach ($this->lists as $list) {
            $bitrixList = new BitrixlistResource($list);
           array_push($bitrixLists, $bitrixList);
        }

        return [
            'id' => $this->id,
            'departament' => $this->getSalesDepartamentId(),
            // 'bitrixList' => $this->getSalesBitrixListId(),
            'bitrixLists' => $bitrixLists,
            'bitrixCallingTasksGroup' => $this->getSalesCallingGroupId(),
            'bitrixSmart' => $this->getSalesSmart(),
            'bitrixDeal' => $this->deal(),
            'smarts' => $resultsmarts,
            // 'smart' => $this->getSalesSmart(),
            'deals' => $this->deals,
            'rpas' => $this->rpas,
            'company' => $this->company(),
            'lead' => $this->lead(),
            'contracts' =>  $this->contracts,
            'measures' =>  $this->measures,
        ];
    }
}
