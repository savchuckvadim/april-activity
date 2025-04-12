<?php

namespace App\Http\Resources\BitrixApp;


use Illuminate\Http\Resources\Json\JsonResource;

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
            'domain' => $this->domain,
            'code' => $this->code,
            'status' => $this->status,
            'placements' => $this->placements,
            'portal' => $this->portal,
            // Токен-данные (можно тоже сделать отдельным ресурсом)
            'client_id' => $token?->getClientId(),
            'client_secret' => $token?->getSecret(),
            'access_token' => $token?->getAccessToken(),
            'refresh_token' => $token?->getRefreshToken(),
            'application_token' => $token?->getApplicationToken(),
            'member_id' => $token?->getMemberId(),
        ];
    }
}
