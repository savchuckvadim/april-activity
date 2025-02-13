<?php
namespace App\Services\Document\DTO\OfferPrice;





class OfferPriceDTO
{
    public function __construct(
        public array $general,
        public array $alternative,
        public array $total
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            array_map(fn($group) => GroupCellsDTO::fromArray($group), $data['general'] ?? []),
            array_map(fn($group) => GroupCellsDTO::fromArray($group), $data['alternative'] ?? []),
            array_map(fn($group) => GroupCellsDTO::fromArray($group), $data['total'] ?? [])
        );
    }
}

// Пример использования
// $jsonString = file_get_contents('data.json'); // JSON-файл или API-ответ
// $dataArray = json_decode($jsonString, true);
// $mainDTO = MainDTO::fromArray($dataArray);

// print_r($mainDTO);
