<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PortalResource extends JsonResource
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
            'domain' => $this->domain,
            'key' => $this->key,
            'C_REST_WEB_HOOK_URL' => $this->C_REST_WEB_HOOK_URL,
            'C_REST_CLIENT_SECRET' => $this->C_REST_CLIENT_SECRET,
            'C_REST_WEB_HOOK_URL' => $this->C_REST_WEB_HOOK_URL,
            // 'key' => $this->getKey(),
            // 'C_REST_CLIENT_ID' => $this->getClientId(),
            // 'C_REST_CLIENT_SECRET' => $this->getSecret(),
            // 'C_REST_WEB_HOOK_URL' => $this->getHook(),
            'providers' => $this->providers,
            'templates' => $this->templates,
            'callingGroups' => $this->callingGroups,
            'smarts' => $this->smarts,
            'deals' => $this->deals,
            'companies' => $this->companies,
            'leads' => $this->leads,
            'rpas' => $this->rpas,
            'bitrixlists' => $this->lists,
            'departaments' => $this->departaments,
            'timezones' => $this->timezones,
            'contracts' =>  $this->contracts,
            'portalmeasures' =>  $this->measures,
        ];
    }
}
