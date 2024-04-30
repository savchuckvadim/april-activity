<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalHookResource extends JsonResource
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
            'domain' => $this->domain,
            'key' => $this->getKey(),
            'C_REST_CLIENT_ID' => $this->getClientId(),
            'C_REST_CLIENT_SECRET' => $this->getSecret(),
            'C_REST_WEB_HOOK_URL' => $this->getHook(),
            'timezone' => $this->getSalesTimezone(),
            'departament' => $this->getSalesDepartamentId(),
            'bitrixList' => $this->getSalesBitrixListId(),
            'bitrixCallingTasksGroup' => $this->getSalesCallingGroupId(),
            'bitrixSmart' => $this->getSalesSmart(),
            'bitrixDeal' => $this->deal(),
            'smarts' => $this->smarts,
            'smart' => $this->smart(),
            // 'deals' => $this->deals(),
            // 'company' => $this->company(),
            // 'lead' => $this->lead(),
        ];
    }
}
