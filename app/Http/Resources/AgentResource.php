<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        // $resultcategories = [];
        // foreach ($this->categories as $ctgr) {
        //     $category = new BtxCategoryResource($ctgr);
        //    array_push($resultcategories, $category);
        // }

        return [
            'id' => $this->id,
            'number' => $this->portal_id,
            'type' => $this->code,
            'portalId' => $this->portalId,
            'name' => $this->name,
            'code' =>  $this->code, 
            'rq' => $this->rq,
            
        ];
    }
}
