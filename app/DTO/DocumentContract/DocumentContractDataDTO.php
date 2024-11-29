<?php

namespace App\DTO\DocumentContract;

use App\Http\Requests\GetContractDocumentRequest;

class DocumentContractDataDTO
{
    public function __construct(
        public string $domain,
        public int $companyId,
        public string $contractType,
        public SupplyDTO $supply,
        public ContractDTO $contract,

        /** @var ProductDTO[] */
        public array $products,

        /** @var RowDTO[] */
        public array $arows,


        public array $contractBaseState,
        public array $contractClientState,
        public array $contractProviderState,
        public int $userId,
        public bool $isSupplyReport,
    ) {}

    public static function fromRequest(GetContractDocumentRequest $request): self
    {
        return new self(
            domain: $request->input('domain'),
            companyId: (int)$request->input('companyId'),
            contractType: $request->input('contractType'),
            supply: SupplyDTO::fromArray($request->input('supply')),
            contract: ContractDTO::fromArray($request->input('contract')),
            products: array_map(
                fn($product) => !empty($product) ? ProductDTO::fromArray($product) : null,
                $request->input('products', [])
            ),
            arows: array_map(fn($row) => RowDTO::fromArray($row), $request->input('arows', [])),
            contractBaseState: $request->input('contractBaseState', []),
            contractClientState: $request->input('contractClientState', []),
            contractProviderState: $request->input('contractProviderState', []),
            userId: (int)$request->input('userId'),
            isSupplyReport: (bool)$request->input('isSupplyReport'),
        );
    }
}
