<?php

namespace App\Services;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BitrixDealUpdateService
{
    protected $domain;
    protected $dealId;
    protected $hook;
    protected $setDealData;
    protected $updateDealInfoblocksData;
    protected $updateDealContractData;
    protected $setProductRowsData;
    protected $updateProductRowsData;


    public function __construct(
        $domain,
        $dealId,
        $setDealData,
        $updateDealInfoblocksData,
        $updateDealContractData,
        $setProductRowsData,
        $updateProductRowsData




    ) {
        $this->domain =  $domain;
        $this->dealId =  $dealId;
        $hook = BitrixController::getHook($domain);
        $this->hook =  $hook;

        $this->setDealData =  $setDealData;
        $this->updateDealInfoblocksData =  $updateDealInfoblocksData;
        $this->updateDealContractData =  $updateDealContractData;


        $this->setProductRowsData =  $setProductRowsData;
        $this->updateProductRowsData =  $updateProductRowsData;
    }


    public function dealProccess()
    {
        $newDeal = null;
        if (!$this->dealId) {
            $newDeal = $this->setDeal();
            $this->dealId = $newDeal;
        }
        sleep(3);
        $updatedDeal = $this->updateDeal();

        $result = [
            '$newDeal' => $newDeal,
            'newDealId' => $this->dealId,
            'updatedDeal' => $updatedDeal
        ];

        return APIController::getSuccess($result);
    }


    protected function setDeal()
    {
        $method = '/crm.deal.add.json';
        $url = $this->hook . $method;


        $response = Http::get($url, $this->setDealData);
        return $this->getBitrixRespone($response);
    }


    protected function updateDeal()
    {
        $method = '/batch';
        $url = $this->hook . $method;


        $this->updateDealInfoblocksData['id'] = $this->dealId;
        $this->updateDealContractData['id'] = $this->dealId;
        $this->updateDealInfoblocksData['ID'] = $this->dealId;
        $this->updateDealContractData['ID'] = $this->dealId;


        $batchData = [
            'halt' => 0, // Продолжать выполнение даже если один из запросов вернет ошибку
            'cmd' => [
                // Здесь указываются команды для выполнения. Ключи - это идентификаторы команд.
            ]
        ];
        
        foreach ($this->updateDealInfoblocksData['fields'] as $fieldKey => $fieldValue) {
            // Закодируем параметры для URL
            $queryParams = http_build_query([
                'id' => $this->dealId,
                'fields' => [$fieldKey => $fieldValue]
            ]);
            $batchData['cmd']["update_iblocks_$fieldKey"] = "crm.deal.update?$queryParams";
        }
        foreach ($this->updateDealInfoblocksData['fields'] as $fieldKey => $fieldValue) {
            // Закодируем параметры для URL
            $queryParams = http_build_query([
                'id' => $this->dealId,
                'fields' => [$fieldKey => $fieldValue]
            ]);
            $batchData['cmd']["update_contract_$fieldKey"] = "crm.deal.update?$queryParams";
        }
        $response = Http::post($url, $batchData);

        // Обработка ответа
        $batchResponse = $response->json();
        // $infoblocksResponse =  $this->getBitrixRespone($responseInfoblocks);
        // $batchResponse =  $this->getBitrixRespone($batchResponse);
        return [
            // 'infoblocksResponse' => $infoblocksResponse,
            'batchResponse' => $batchResponse,
        ];
    }

    protected function productsSet()
    {
        $method = '/crm.item.productrow.set.json';
        $url = $this->hook . $method;
        $response = Http::get($url, $this->setProductRowsData);
        return $this->getBitrixRespone($response);
    }


    protected function productsUpdate()
    {
        $method = '/crm.item.productrow.update.json';
        $url = $this->hook . $method;
        $response = Http::get($url, [
            //todo 
        ]);
        return $this->getBitrixRespone($response);
    }



    protected function getBitrixRespone($bitrixResponse)
    {
        $response =  $bitrixResponse->json();
        if ($response) {
            if (isset($response['result'])) {

                Log::info('success btrx response', [
                    'BTRX_RESPONSE_SUCCESS' => [
                        'domain' => $this->domain,
                        'result' => $response['result'],

                    ]

                ]);
                return $response['result'];
            } else {
                if (isset($response['error_description'])) {
                    Log::info('error', [
                        'SET_DEAL' => [
                            'domain' => $this->domain,
                            'btrx error' => $response['error'],
                            'btrx response' => $response['error_description']
                        ]

                    ]);
                    return null;
                }
            }
        }
    }
}
