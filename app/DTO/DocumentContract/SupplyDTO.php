<?php

namespace App\DTO\DocumentContract;

class SupplyDTO
{
    public string $acontractName;
    public string $acontractPropComment;
    public float $coefficient;
    public string $contractName;
    public string $contractProp1;
    public string $contractProp2;
    public string $contractPropComment;
    public string $contractPropEmail;
    public string $contractPropLoginsQuantity;
    public string $contractPropSuppliesQuantity;
    public bool $lcontractName;
    public bool $lcontractProp2;
    public bool $lcontractPropComment;
    public bool $lcontractPropEmail;
    public string $name;
    public int $number;
    public string $quantityForKp;
    public string $type;

    public function __construct(array $data)
    {
        $this->acontractName = $data['acontractName'] ?? '';
        $this->acontractPropComment = $data['acontractPropComment'] ?? '';
        $this->coefficient = (float) ($data['coefficient'] ?? 0);
        $this->contractName = $data['contractName'] ?? '';
        $this->contractProp1 = $data['contractProp1'] ?? '';
        $this->contractProp2 = $data['contractProp2'] ?? '';
        $this->contractPropComment = $data['contractPropComment'] ?? '';
        $this->contractPropEmail = $data['contractPropEmail'] ?? '';
        $this->contractPropLoginsQuantity = $data['contractPropLoginsQuantity'] ?? '';
        $this->contractPropSuppliesQuantity = $data['contractPropSuppliesQuantity'] ?? '';
        $this->lcontractName = (bool) ($data['lcontractName'] ?? false);
        $this->lcontractProp2 = (bool) ($data['lcontractProp2'] ?? false);
        $this->lcontractPropComment = (bool) ($data['lcontractPropComment'] ?? false);
        $this->lcontractPropEmail = (bool) ($data['lcontractPropEmail'] ?? false);
        $this->name = $data['name'] ?? '';
        $this->number = (int) ($data['number'] ?? 0);
        $this->quantityForKp = $data['quantityForKp'] ?? '';
        $this->type = $data['type'] ?? '';
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
