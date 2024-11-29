<?php

namespace App\DTO;

use App\Http\Requests\GetContractDocumentRequest;

class DocumentContractDataDTO
{
    public function __construct(
        public string $domain,
        public int $companyId,
        public string $contractType,
        public array $supply,
        public array $contract,
        public array $productSet,
        public array $products,
        public array $arows,
        public array $contractBaseState,
        public array $contractClientState,
        public array $contractProviderState,
        public int $userId,
        public bool $isSupplyReport
    ) {}

    public static function fromRequest(GetContractDocumentRequest $request): self
    {
        return new self(
            $request->input('domain'),
            $request->input('companyId'),
            $request->input('contractType'),
            $request->input('supply'),
            $request->input('contract'),
            $request->input('productSet'),
            $request->input('products'),
            $request->input('arows'),
            $request->input('contractBaseState'),
            $request->input('contractClientState'),
            $request->input('contractProviderState'),
            $request->input('userId'),
            $request->input('isSupplyReport')
        );
    }
}
