<?php

namespace App\DTO\DocumentContract;

use App\Models\Contract;
use App\Models\PortalContract;
use App\Models\PortalMeasure;



class ContractDTO
{
    public int $id;
    public Contract $contract;
    public string $code;
    public string $shortName;
    public int $number;
    public string $aprilName;
    public string $bitrixName;
    public float $discount;
    public int $itemId;
    public float $prepayment;
    public int $order;
    public PortalMeasure $portalMeasure;
    public int $measureCode;
    public string $measureFullName;
    public int $measureId;
    public string $measureName;
    public int $measureNumber;

    public function __construct(
        int $id,
        Contract $contract,
        string $code,
        string $shortName,
        int $number,
        string $aprilName,
        string $bitrixName,
        float $discount,
        int $itemId,
        float $prepayment,
        int $order,
        PortalMeasure $portalMeasure,
        int $measureCode,
        string $measureFullName,
        int $measureId,
        string $measureName,
        int $measureNumber
    ) {
        $this->id = $id;
        $this->contract = $contract;
        $this->code = $code;
        $this->shortName = $shortName;
        $this->number = $number;
        $this->aprilName = $aprilName;
        $this->bitrixName = $bitrixName;
        $this->discount = $discount;
        $this->itemId = $itemId;
        $this->prepayment = $prepayment;
        $this->order = $order;
        $this->portalMeasure = $portalMeasure;
        $this->measureCode = $measureCode;
        $this->measureFullName = $measureFullName;
        $this->measureId = $measureId;
        $this->measureName = $measureName;
        $this->measureNumber = $measureNumber;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            contract: Contract::findOrFail($data['contract']['id']),
            code: $data['code'],
            shortName: $data['shortName'],
            number: $data['number'],
            aprilName: $data['aprilName'],
            bitrixName: $data['bitrixName'],
            discount: (float) $data['discount'],
            itemId: $data['itemId'],
            prepayment: (float) $data['prepayment'],
            order: $data['order'],
            portalMeasure: PortalMeasure::findOrFail($data['portalMeasure']['id']),
            measureCode: $data['measureCode'],
            measureFullName: $data['measureFullName'],
            measureId: $data['measureId'],
            measureName: $data['measureName'],
            measureNumber: $data['measureNumber']
        );
    }
}

class InnerContractDTO
{
    public int $id;
    public string $created_at;
    public string $updated_at;
    public string $name;
    public int $number;
    public string $title;
    public string $code;
    public string $type;
    public ?string $template;
    public int $order;
    public float $coefficient;
    public float $prepayment;
    public float $discount;
    public string $productName;
    public ?string $product;
    public ?string $service;
    public ?string $description;
    public ?string $comment;
    public ?string $comment1;
    public ?string $comment2;
    public int $withPrepayment;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->created_at = $data['created_at'];
        $this->updated_at = $data['updated_at'];
        $this->name = $data['name'];
        $this->number = $data['number'];
        $this->title = $data['title'];
        $this->code = $data['code'];
        $this->type = $data['type'];
        $this->template = $data['template'];
        $this->order = $data['order'];
        $this->coefficient = $data['coefficient'];
        $this->prepayment = $data['prepayment'];
        $this->discount = $data['discount'];
        $this->productName = $data['productName'];
        $this->product = $data['product'];
        $this->service = $data['service'];
        $this->description = $data['description'];
        $this->comment = $data['comment'];
        $this->comment1 = $data['comment1'];
        $this->comment2 = $data['comment2'];
        $this->withPrepayment = $data['withPrepayment'];
    }
}

class PortalMeasureDTO
{
    public int $id;
    public int $measure_id;
    public int $portal_id;
    public string $bitrixId;
    public string $name;
    public string $shortName;
    public string $fullName;
    public string $created_at;
    public string $updated_at;
    public MeasureDTO $measure;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->measure_id = $data['measure_id'];
        $this->portal_id = $data['portal_id'];
        $this->bitrixId = $data['bitrixId'];
        $this->name = $data['name'];
        $this->shortName = $data['shortName'];
        $this->fullName = $data['fullName'];
        $this->created_at = $data['created_at'];
        $this->updated_at = $data['updated_at'];
        $this->measure = new MeasureDTO($data['measure']);
    }
}

class MeasureDTO
{
    public int $id;
    public string $created_at;
    public string $updated_at;
    public string $name;
    public string $shortName;
    public string $fullName;
    public string $code;
    public string $type;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->created_at = $data['created_at'];
        $this->updated_at = $data['updated_at'];
        $this->name = $data['name'];
        $this->shortName = $data['shortName'];
        $this->fullName = $data['fullName'];
        $this->code = $data['code'];
        $this->type = $data['type'];
    }
}
