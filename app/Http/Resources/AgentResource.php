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
        $rq = $this->rq;
        if (!empty($rq)) {
            $rq = [];
        }
        return [
            'id' => $this->id,
            'number' => $this->number,
            'type' => $this->type,
            'portalId' => $this->portalId,
            'name' => $this->name,
            'code' =>  $this->code,
            'rq' => $rq,

        ];
    }
}
