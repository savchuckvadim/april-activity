<?php

namespace App\DTO\DocumentContract;

class RowDTO
{
    public int $number;
    public string $name;
    public string $shortName;
    public string $type;
    public string $productType;
    public int $id;
    public int $setId;
    public bool $isUpdating;

    public RowComplectDTO $complect;
    public RowContractDTO $contract;
    public RowSupplyDTO $supply;
    public RowPriceDTO $price;
    public ProductDTO $product;
    public SupplyDTO $currentSupply;

    public function __construct(array $data)
    {
        $this->number = $data['number'];
        $this->name = $data['name'];
        $this->shortName = $data['shortName'];
        $this->type = $data['type'];
        $this->productType = $data['productType'];
        $this->id = $data['id'];
        $this->setId = $data['setId'];
        $this->isUpdating = $data['isUpdating'];

        $this->complect = new RowComplectDTO($data['complect']);
        $this->contract = new RowContractDTO($data['contract']);
        $this->supply = new RowSupplyDTO($data['supply']);
        $this->price = new RowPriceDTO($data['price']);
        $this->product = ProductDTO::fromArray($data['product']);
        $this->currentSupply = new SupplyDTO($data['currentSupply']);
    }

       /**
     * Create an instance of RowDTO from an array.
     *
     * @param array $data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}

class RowComplectDTO
{
    public string $type;
    public int $number;

    public function __construct(array $data)
    {
        $this->type = $data['type'];
        $this->number = $data['number'];
    }
}

class RowContractDTO
{
    public string $name;
    public int $number;

    public function __construct(array $data)
    {
        $this->name = $data['name'];
        $this->number = $data['number'];
    }
}

class RowSupplyDTO
{
    public ?string $name;
    public ?string $forkp;
    public int $number;
    public string $type;
    public ?string $contractName;
    public ?string $contractProp2;
    public ?string $contractProp1;
    public ?string $contractPropComment;
    public ?string $contractPropEmail;

    public function __construct(array $data)
    {
        $this->name = $data['name'] ?? '';
        $this->forkp = $data['forkp'] ?? '';
        $this->number = $data['number'];
        $this->type = $data['type'];
        $this->contractName = $data['contractName'] ?? '';
        $this->contractProp2 = $data['contractProp2'] ?? '';
        $this->contractProp1 = $data['contractProp1'] ?? '';
        $this->contractPropComment = $data['contractPropComment'] ?? '';
        $this->contractPropEmail = $data['contractPropEmail'] ?? '';
    }
}

class RowPriceDTO
{
    public float $default;
    public float $current;
    public RowMeasureDTO $measure;
    public float $month;
    public int $quantity;
    public RowDiscountDTO $discount;
    public float $sum;

    public function __construct(array $data)
    {
        $this->default = $data['default'];
        $this->current = $data['current'];
        $this->measure = new RowMeasureDTO($data['measure']);
        $this->month = $data['month'];
        $this->quantity = $data['quantity'];
        $this->discount = new RowDiscountDTO($data['discount']);
        $this->sum = $data['sum'];
    }
}

class RowMeasureDTO
{
    public int $id;
    public int $code;
    public int $type;
    public string $name;
    public int $contractNumber;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->code = $data['code'];
        $this->type = $data['type'];
        $this->name = $data['name'];
        $this->contractNumber = $data['contractNumber'];
    }
}

class RowDiscountDTO
{
    public float $precent;
    public float $amount;
    public string $current;

    public function __construct(array $data)
    {
        $this->precent = $data['precent'];
        $this->amount = $data['amount'];
        $this->current = $data['current'];
    }
}


