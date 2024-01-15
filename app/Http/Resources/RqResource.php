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
            'type' => $this->type,
            'fullname' => $this->fullname,
            'shortname' => $this->shortname,
            'director' => $this->director,
            'position' => $this->position,
            'accountant' => $this->accountant,
            'based' => $this->based,
            'inn' => $this->inn,
            'kpp' => $this->kpp,
            'ogrn' => $this->ogrn,
            'ogrnip' => $this->ogrnip,
            'personName' => $this->personName,
            'document' => $this->document,
            'docSer' => $this->docSer,
            'docNum' => $this->docNum,
            'docDate' => $this->docDate,
            'docIssuedBy' => $this->docIssuedBy,
            'docDepCode' => $this->docDepCode,
            'registredAdress' => $this->registredAdress,
            'primaryAdresss' => $this->primaryAdresss,
            'email' => $this->email,
            'garantEmail' => $this->garantEmail,
            'phone' => $this->phone,
            'assigned' => $this->assigned,
            'assignedPhone' => $this->assignedPhone,
            'other' => $this->other,
            'bank' => $this->bank,
            'bik' => $this->bik,
            'rs' => $this->rs,
            'ks' => $this->ks,
            'bankAdress' => $this->bankAdress,
            'bankOther' =>  $this->bankOther,
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

            'agent' => $this->agent
            // Включите другие связанные данные по необходимости
        ];
    }
}
