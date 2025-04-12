<?php

namespace App\Http\Resources\BitrixApp;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BitrixAppTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getSecret(),
            'access_token' => $this->getAccessToken(),
            'refresh_token' => $this->getRefreshToken(),
            'application_token' => $this->getApplicationToken(),
            'member_id' => $this->getMemberId(),
            'expires_at' => $this->expires_at,
        ];
    }
}
