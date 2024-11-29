<?php

namespace App\DTO\DocumentContract\Complect;

class ComplectDTO
{
    public string $groupsName;
    /** @var ComplectValueDTO[] */
    public array $value;

    public function __construct(array $data)
    {
        $this->groupsName = $data['groupsName'];
        $this->value = array_map(fn($item) => new ComplectValueDTO($item), $data['value']);
    }
}
