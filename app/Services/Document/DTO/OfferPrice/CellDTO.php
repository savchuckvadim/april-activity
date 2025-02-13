<?php
namespace App\Services\Document\DTO\OfferPrice;





class CellDTO
{
    public function __construct(
        public string $name,
        public string $code,
        public bool $isActive,
        public string $type,
        public int $order,
        public mixed $defaultValue,
        public mixed $value,
        public string $target,
        public ?SupplyDTO $supply = null
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? '',
            $data['code'] ?? '',
            $data['isActive'] ?? false,
            $data['type'] ?? '',
            $data['order'] ?? 0,
            $data['defaultValue'] ?? null,
            $data['value'] ?? null,
            $data['target'] ?? '',
            isset($data['supply']) ? SupplyDTO::fromArray($data['supply']) : null
        );
    }
}
