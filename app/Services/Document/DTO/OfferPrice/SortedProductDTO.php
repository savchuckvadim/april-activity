<?php
declare(strict_types=1);

namespace App\Services\Document\DTO\OfferPrice;

class SortedProductDTO
{
    public function __construct(
        public string $name,
        public array $cells // массив из CellDTO
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            cells: array_map(fn($cell) => CellDTO::fromArray($cell), $data['cells'])
        );
    }
}
