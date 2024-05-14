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

        return [
            'id' => $this->id,
            'departament' => $this->getSalesDepartamentId(),
            'bitrixList' => $this->getSalesBitrixListId(),
            'bitrixCallingTasksGroup' => $this->getSalesCallingGroupId(),
            'bitrixSmart' => $this->getSalesSmart(),
            'bitrixDeal' => $this->deal(),
            'smarts' => $resultsmarts,
            // 'smart' => $this->getSalesSmart(),
            'deals' => $this->deals,
            'company' => $this->company(),
            'lead' => $this->lead(),
        ];
    }
}
