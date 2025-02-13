<?php
namespace App\Services\Document\DTO\OfferPrice;

class GroupCellsDTO
{
    public function __construct(
        public string $name,
        public string $target,
        public array $cells
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['name'] ?? '',
            $data['target'] ?? '',
            array_map(fn($cell) => CellDTO::fromArray($cell), $data['cells'] ?? [])
        );
    }
}
