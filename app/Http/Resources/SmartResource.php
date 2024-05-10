<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SmartResource extends JsonResource
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

        // $resultfields= [];
        // foreach ($this->fields as $fld) {
        //     $field = new BitrixFieldResource($fld);
        //    array_push($resultfields, $field);
        // }

        return [
            'id' => $this->id,
            'portal_id' => $this->portal_id,
            'type' => $this->type,
            'group' => $this->group,
            'name' => $this->name,
            'title' => $this->title,
            'bitrixId' => $this->bitrixId,
            'entityTypeId' => $this->entityTypeId,
            'forStageId' => $this->forStageId,
            'forStage' => $this->forStage,
            'crmId' => $this->crmId,
            'crm' => $this->crm,
            'forFilterId' => $this->forFilterId,
            'forFilter' => $this->forFilter,
            // 'categories' =>  $resultcategories, 
            // 'bitrixfields' => $resultfields,
            'categories' => !empty($this->categories) ? BtxCategoryResource::collection($this->categories) : [],
            'fields' => !empty($this->fields)
                ? BitrixFieldResource::collection($this->fields)
                : [],


        ];
    }
}
