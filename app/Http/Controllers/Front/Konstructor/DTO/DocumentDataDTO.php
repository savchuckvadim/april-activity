<?php

namespace App\Http\Controllers\Front\Konstructor\DTO;

class DocumentDataDTO
{
    public string $domain;
    public int $companyId;
    public string $contractType;
    public ?array $supply;
    public ?array $contract;
    public bool $isSupplyReport;

    public function __construct(array $data)
    {
        $this->domain = $data['domain'];
        $this->companyId = $data['companyId'];
        $this->contractType = $data['contractType'];
        $this->supply = $data['supply'] ?? null;
        $this->contract = $data['contract'] ?? null;
        $this->isSupplyReport = $data['isSupplyReport'] ?? false;
    }
}
