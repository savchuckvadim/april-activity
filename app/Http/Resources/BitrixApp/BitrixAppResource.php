<?php

namespace App\Http\Resources\BitrixApp;


use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\PortalResource;
use App\Http\Resources\BitrixApp\BitrixAppTokenResource;



class BitrixAppResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $token = $this->whenLoaded('token');

        return [
            'app_id' => $this->id,
            'domain' => $this->portal?->domain, // domain из Portal
            'code' => $this->code,
            'status' => $this->status,
            'placements' => $this->whenLoaded('placements'),
            // 'portal' => new PortalResource($this->whenLoaded('portal')),
    
            // Токен-данные (можно тоже сделать отдельным ресурсом)
            'token' => new BitrixAppTokenResource($this->whenLoaded('token')),

        ];
    }
}
