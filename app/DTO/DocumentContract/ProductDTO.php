<?php

namespace App\DTO\DocumentContract;

use App\Http\Controllers\ALog;

class ProductDTO
{
    public int $number;
    public string $name;
    public ?int $productId;
    public string $type;
    public int $complectNumber;
    public string $complectName;
    public bool $withConsalting;
    public ?string $complectType;
    public bool $abs;
    public int $supplyNumber;
    public ?string $supplyName;
    public ?string $supplyType;
    public ?string $quantityForKp;
    public array|bool $supply;
    public ?string $contractSupplyName;
    public ?string $contractSupplyProp1;
    public ?string $contractSupplyProp2;
    public ?string $contractSupplyPropComment;
    public ?string $contractSupplyPropEmail;
    public ?string $contractSupplyPropLoginsQuantity;
    public ?string $contractSupplyPropSuppliesQuantity;
    public ContractDTO $contract;
    public string $contractName;
    public string $contractShortName;
    public int $contractNumber;
    public int $measureNumber;
    public int $measureId;
    public int $measureCode;
    public string $measureName;
    public string $measureFullName;
    public float $prepayment;
    public float $contractCoefficient;
    public float $discount;
    public ?string $contractConsaltingProp;
    public ?string $contractConsaltingComment;
    public bool $withPrice;
    public bool $withAbs;
    public float $price;
    public float $totalPrice;
    public bool $mskPrice;
    public bool $regionsPrice;

    public function __construct(
        int $number,
        string $name,
        ?int $productId,
        string $type,
        int $complectNumber,
        string $complectName,
        bool $withConsalting,
        string $complectType,
        bool $abs,
        int $supplyNumber,
        ?string $supplyName,
        ?string $supplyType,
        ?string $quantityForKp,
        array|bool $supply,
        ?string $contractSupplyName,
        ?string $contractSupplyProp1,
        ?string $contractSupplyProp2,
        ?string $contractSupplyPropComment,
        ?string $contractSupplyPropEmail,
        ?string $contractSupplyPropLoginsQuantity,
        ?string $contractSupplyPropSuppliesQuantity,
        ContractDTO $contract,
        string $contractName,
        string $contractShortName,
        int $contractNumber,
        int $measureNumber,
        int $measureId,
        int $measureCode,
        string $measureName,
        string $measureFullName,
        float $prepayment,
        float $contractCoefficient,
        float $discount,
        ?string $contractConsaltingProp,
        ?string $contractConsaltingComment,
        bool $withPrice,
        bool $withAbs,
        float $price,
        float $totalPrice,
        bool $mskPrice,
        bool $regionsPrice
    ) {
        $this->number = $number;
        $this->name = $name;
        $this->productId = $productId;
        $this->type = $type;
        $this->complectNumber = $complectNumber;
        $this->complectName = $complectName;
        $this->withConsalting = $withConsalting;
        $this->complectType = $complectType;
        $this->abs = $abs;
        $this->supplyNumber = $supplyNumber;
        $this->supplyName = $supplyName;
        $this->supplyType = $supplyType;
        $this->quantityForKp = $quantityForKp;
        $this->supply = $supply;
        $this->contractSupplyName = $contractSupplyName;
        $this->contractSupplyProp1 = $contractSupplyProp1;
        $this->contractSupplyProp2 = $contractSupplyProp2;
        $this->contractSupplyPropComment = $contractSupplyPropComment;
        $this->contractSupplyPropEmail = $contractSupplyPropEmail;
        $this->contractSupplyPropLoginsQuantity = $contractSupplyPropLoginsQuantity;
        $this->contractSupplyPropSuppliesQuantity = $contractSupplyPropSuppliesQuantity;
        $this->contract = $contract;
        $this->contractName = $contractName;
        $this->contractShortName = $contractShortName;
        $this->contractNumber = $contractNumber;
        $this->measureNumber = $measureNumber;
        $this->measureId = $measureId;
        $this->measureCode = $measureCode;
        $this->measureName = $measureName;
        $this->measureFullName = $measureFullName;
        $this->prepayment = $prepayment;
        $this->contractCoefficient = $contractCoefficient;
        $this->discount = $discount;
        $this->contractConsaltingProp = $contractConsaltingProp;
        $this->contractConsaltingComment = $contractConsaltingComment;
        $this->withPrice = $withPrice;
        $this->withAbs = $withAbs;
        $this->price = $price;
        $this->totalPrice = $totalPrice;
        $this->mskPrice = $mskPrice;
        $this->regionsPrice = $regionsPrice;
    }

    public static function fromArray(array $data): self
    {
        ALog::push('supply from productDTO', $data);
        return new self(
            number: $data['number'],
            name: $data['name'],
            productId: $data['productId'] ?? null,
            type: $data['type'],
            complectNumber: $data['complectNumber'],
            complectName: $data['complectName'],
            withConsalting: $data['withConsalting'],
            complectType: $data['complectType'],
            abs: $data['abs'],
            supplyNumber: $data['supplyNumber'],
            supplyName: $data['supplyName'],
            supplyType: $data['supplyType'],
            quantityForKp: $data['quantityForKp'],
            supply: $data['supply'],
            contractSupplyName: $data['contractSupplyName'],
            contractSupplyProp1: $data['contractSupplyProp1'],
            contractSupplyProp2: $data['contractSupplyProp2'],
            contractSupplyPropComment: $data['contractSupplyPropComment'],
            contractSupplyPropEmail: $data['contractSupplyPropEmail'],
            contractSupplyPropLoginsQuantity: $data['contractSupplyPropLoginsQuantity'],
            contractSupplyPropSuppliesQuantity: $data['contractSupplyPropSuppliesQuantity'],
            contract: ContractDTO::fromArray($data['contract']),
            contractName: $data['contractName'],
            contractShortName: $data['contractShortName'],
            contractNumber: $data['contractNumber'],
            measureNumber: $data['measureNumber'],
            measureId: $data['measureId'],
            measureCode: $data['measureCode'],
            measureName: $data['measureName'],
            measureFullName: $data['measureFullName'],
            prepayment: $data['prepayment'],
            contractCoefficient: $data['contractCoefficient'],
            discount: $data['discount'],
            contractConsaltingProp: $data['contractConsaltingProp'],
            contractConsaltingComment: $data['contractConsaltingComment'],
            withPrice: $data['withPrice'],
            withAbs: $data['withAbs'],
            price: $data['price'],
            totalPrice: $data['totalPrice'],
            mskPrice: $data['mskPrice'],
            regionsPrice: $data['regionsPrice']
        );
    }
}


// class ProductContractDTO
// {
//     public int $id;
//     public InnerContractDTO $contract;
//     public string $code;
//     public string $shortName;
//     public int $number;
//     public string $aprilName;
//     public string $bitrixName;
//     public float $discount;
//     public int $itemId;
//     public float $prepayment;
//     public int $order;
//     public PortalMeasureDTO $portalMeasure;
//     public int $measureCode;
//     public string $measureFullName;
//     public int $measureId;
//     public string $measureName;
//     public int $measureNumber;

//     public function __construct(array $data)
//     {
//         $this->id = $data['id'];
//         $this->contract = new InnerContractDTO($data['contract']);
//         $this->code = $data['code'];
//         $this->shortName = $data['shortName'];
//         $this->number = $data['number'];
//         $this->aprilName = $data['aprilName'];
//         $this->bitrixName = $data['bitrixName'];
//         $this->discount = $data['discount'];
//         $this->itemId = $data['itemId'];
//         $this->prepayment = $data['prepayment'];
//         $this->order = $data['order'];
//         $this->portalMeasure = new PortalMeasureDTO($data['portalMeasure']);
//         $this->measureCode = $data['measureCode'];
//         $this->measureFullName = $data['measureFullName'];
//         $this->measureId = $data['measureId'];
//         $this->measureName = $data['measureName'];
//         $this->measureNumber = $data['measureNumber'];
//     }
// }

// class InnerContractDTO
// {
//     public int $id;
//     public string $name;
//     public int $number;

//     public function __construct(array $data)
//     {
//         $this->id = $data['id'];
//         $this->name = $data['name'];
//         $this->number = $data['number'];
//     }
// }

// class PortalMeasureDTO
// {
//     public int $id;
//     public int $measure_id;
//     public int $portal_id;
//     public string $bitrixId;
//     public string $name;
//     public string $shortName;
//     public string $fullName;

//     public function __construct(array $data)
//     {
//         $this->id = $data['id'];
//         $this->measure_id = $data['measure_id'];
//         $this->portal_id = $data['portal_id'];
//         $this->bitrixId = $data['bitrixId'];
//         $this->name = $data['name'];
//         $this->shortName = $data['shortName'];
//         $this->fullName = $data['fullName'];
//     }
// }
