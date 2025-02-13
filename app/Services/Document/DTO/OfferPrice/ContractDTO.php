<?php
namespace App\Services\Document\DTO\OfferPrice;





class ContractDTO
{
    public function __construct(
        public int $id,
        public string $name,
        public string $title,
        public string $code,
        public string $type,
        public ?string $productName,
        public ?int $prepayment,
        public ?int $discount
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'] ?? 0,
            $data['name'] ?? '',
            $data['title'] ?? '',
            $data['code'] ?? '',
            $data['type'] ?? '',
            $data['productName'] ?? null,
            $data['prepayment'] ?? null,
            $data['discount'] ?? null
        );
    }
}
