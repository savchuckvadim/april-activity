<?php

namespace App\DTO\DocumentContract;

use App\Http\Requests\GetContractDocumentRequest;

class DocumentContractDataDTO
{
    public function __construct(
        public string $domain,
        public int $companyId,
        public int $dealId,
        public string $contractType,
        public SupplyDTO $supply,
        public ContractDTO $contract,

        /** @var ProductDTO[] */
        public array $products,

        /** @var RowDTO[] */
        public array $arows,
        public array $clientType,


        public array $contractBaseState,
        // public array $contractClientState,
        public array $bxrq,

        public array $contractProviderState,
        public array $contractSpecificationState,
        public array $total,
        public int $userId,
        public bool $isSupplyReport,
    ) {}

    public static function fromRequest(GetContractDocumentRequest $request): self
    {
        return new self(
            domain: $request->input('domain'),
            contractSpecificationState: $request->input('contractSpecificationState'),
            total: $request->input('total'),
            companyId: (int)$request->input('companyId'),
            dealId: (int)$request->input('dealId'),

            contractType: $request->input('contractType'),
            supply: SupplyDTO::fromArray($request->input('supply')),
            contract: ContractDTO::fromArray($request->input('contract')),
            products: array_map(
                fn($product) => !empty($product) ? ProductDTO::fromArray($product) : null,
                $request->input('products', [])
            ),
            arows: array_map(fn($row) => RowDTO::fromArray($row), $request->input('arows', [])),
            
            clientType: $request->input('clientType', []),
            contractBaseState: $request->input('contractBaseState', []),
            // contractClientState: $request->input('contractClientState', []),
            bxrq: $request->input('bxrq', []),
            contractProviderState: $request->input('contractProviderState', []),
            userId: (int)$request->input('userId'),
            isSupplyReport: (bool)$request->input('isSupplyReport'),
        );
    }
}
