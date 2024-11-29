<?php

namespace App\DTO\DocumentContract\Complect;

class ComplectValueDTO
{
    public ?int $number;
    public string $name;
    public string $code;
    public bool $checked;
    public ?float $weight;
    public ?string $description;
    public ?bool $isLa;

    public function __construct(array $data)
    {
        $this->number = $data['number'] ?? null;
        $this->name = $data['name'];
        $this->code = $data['code'];
        $this->checked = $data['checked'];
        $this->weight = $data['weight'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->isLa = $data['isLa'] ?? null;
    }
}
