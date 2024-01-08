<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $portal =  $this->portal;
        $domain = $portal->domain;
        $providers =  $portal->providers;
        $providersCollection = new ProviderCollection($providers);
        $fields = [];
        $resultFields = [];
        if ($this->fields) {
            $fields = $this->fields;
        }
        $fieldsCollection = new FieldCollection($fields);
        $resultFields = $fieldsCollection->toArray()['fields'];      

        return [
            'id' => $this->id,

            'name' => $this->name,
            'code' => $this->code,
            'type' => $this->type,
            'link' => $this->link,
            // 'portalId' => $this->portalId,
            'portal' => $domain,
            'providers' => $providersCollection,
            'fields' =>  $resultFields,



        ];
    }
}
