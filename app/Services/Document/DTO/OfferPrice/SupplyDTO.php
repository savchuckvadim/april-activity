<?php
namespace App\Services\Document\DTO\OfferPrice;





class SupplyDTO
{
    public function __construct(
        public int $contractPropSuppliesQuantity,
        public string $name,
        public string $type,
        public float $coefficient
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['contractPropSuppliesQuantity'] ?? 0,
            $data['name'] ?? '',
            $data['type'] ?? '',
            $data['coefficient'] ?? 0.0
        );
    }
}
