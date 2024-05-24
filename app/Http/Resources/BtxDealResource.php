<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BtxDealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $resultcategories = [];
        foreach ($this->categories as $ctgr) {
            $category = new BtxCategoryResource($ctgr);
           array_push($resultcategories, $category);
        }

        // $resultfields= [];
        // foreach ($this->fields as $fld) {
        //     $field = new BitrixFieldResource($fld);
        //    array_push($resultfields, $field);
        // }
        return [
            'id' => $this->id,
            'portal_id' => $this->portal_id,
            'code' => $this->code,
            'name' => $this->name,
            'title' => $this->title,
            'categories' =>  $resultcategories, 
            'bitrixfields' => $this->bitrixfields,
            
        ];
    }
}
