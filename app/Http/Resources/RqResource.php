<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RqResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'number' => $this->number,
            'name' => $this->name,
            // Добавьте все другие атрибуты, которые вам нужны
            'logos' => $this->logos->map(function ($logo) {
                return [
                    'name' => $logo->name,
                    'code' => $logo->code,
                    'type' => $logo->type,
                    'path' => $logo->path,
                    // Добавьте другие поля из $fillable в модели 'File', которые вам нужны
                ];
            }),
            'stamps' => $this->stamps->map(function ($stamp) {
                return [
                    'name' => $stamp->name,
                    'code' => $stamp->code,
                    'type' => $stamp->type,
                    'path' => $stamp->path,
                    // Добавьте другие поля из $fillable в модели 'File', которые вам нужны
                ];
            }),
            'signatures' => $this->signatures->map(function ($signature) {
                return [
                    'name' => $signature->name,
                    'code' => $signature->code,
                    'type' => $signature->type,
                    'path' => $signature->path,
                    // Добавьте другие поля из $fillable в модели 'File', которые вам нужны
                ];
            }),
          
            'agent' => $this->agents(),
            // Включите другие связанные данные по необходимости
        ];
    }
}
