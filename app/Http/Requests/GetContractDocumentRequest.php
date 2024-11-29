<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetContractDocumentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'domain' => 'required|string',
            'companyId' => 'required|integer',
            'contractType' => 'required|string',
            'supply.type' => 'required|string|in:internet,proxima',
            'contract' => 'required|array',
            'contract.contract' => 'required|array',
            'contract.contract.coefficient' => 'required|numeric',
            'contract.contract.productName' => 'required|string',
            'contract.prepayment' => 'required|numeric',
            'productSet' => 'required|array',
            'productSet.total' => 'required|array',
            'products' => 'required|array',
            'arows' => 'required|array',
            'contractBaseState.items' => 'required|array',
            'contractClientState.client' => 'required|array',
            'contractClientState.client.rqs.rq' => 'required|array',
            'contractClientState.client.rqs.bank' => 'required|array',
            'contractProviderState' => 'required|array',
            'contractProviderState.current.rq' => 'required|array',
            'userId' => 'required|integer',
        ];
    }
}
