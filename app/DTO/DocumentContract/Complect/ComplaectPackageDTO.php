<?php

namespace App\DTO;

class ComplectPackageDTO
{
    public int $number;
    public ?int $regions;
    public ?int $msk;
    public string $name;
    public string $fullName;
    public ?string $description;
    public int $weight;
    public string $type;

    public function __construct(array $data)
    {
        $this->number = $data['number'];
        $this->regions = $data['regions'] ?? null;
        $this->msk = $data['msk'] ?? null;
        $this->name = $data['name'];
        $this->fullName = $data['fullName'];
        $this->description = $data['description'] ?? null;
        $this->weight = $data['weight'];
        $this->type = $data['type'];
    }
}
