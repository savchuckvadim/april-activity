<?php

namespace App\Services;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BitrixDealUpdateService
{
    protected $domain;
    protected $placement;

    protected $dealId;
    protected $hook;
    protected $updateDealInfoblocksData;
    protected $updateDealContractData;
    protected $setProductRowsData;
    protected $updateProductRowsData;


    public function __construct(
        $domain,
        $placement,
        $dealId,

        $updateDealInfoblocksData,
        $updateDealContractData,
        $setProductRowsData,
        $updateProductRowsData




    ) {
        $this->domain =  $domain;
        $this->placement =  $placement;
        $this->dealId =  $dealId;
        $hook = BitrixController::getHook($domain);
        $this->hook =  $hook;

        // $this->setDealData =  $setDealData;
        $this->updateDealInfoblocksData =  $updateDealInfoblocksData;
        $this->updateDealContractData =  $updateDealContractData;


        $this->setProductRowsData =  $setProductRowsData;
        $this->updateProductRowsData =  $updateProductRowsData;
    }


    public function dealProccess()
    {
        // $newDeal = null;
        // if (!$this->dealId) {
        //     $newDeal = $this->setDeal();
        //     $this->dealId = $newDeal;
        // }

        $updatedDeal = $this->updateDealBitrixDealUpdate();
        $setProductRows = $this->productsSet();
        $result = [
            // '$newDeal' => $newDeal,
            'newDealId' => $this->dealId,
            'updatedDeal' => $updatedDeal,
            'setProductRows' => $setProductRows
        ];

        return APIController::getSuccess($result);
    }


    // protected function setDeal()
    // {
    //     $method = '/crm.deal.add.json';
    //     $url = $this->hook . $method;


    //     $response = Http::get($url, $this->setDealData);
    //     return $this->getBitrixRespone($response);
    // }


    protected function updateDealBitrixDealUpdate()
    {
        $method = '/batch';
        $url = $this->hook . $method;


        $this->updateDealInfoblocksData['id'] = $this->dealId;
        $this->updateDealContractData['id'] = $this->dealId;
        $this->updateDealInfoblocksData['ID'] = $this->dealId;
        $this->updateDealContractData['ID'] = $this->dealId;

        $batchResponse = [];
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
        foreach ($this->updateDealContractData['fields'] as $fieldKey => $fieldValue) {
            // Закодируем параметры для URL
            $queryParams = http_build_query([
                'id' => $this->dealId,
                'fields' => [$fieldKey => $fieldValue]
            ]);
            $batchData['cmd']["update_contract_$fieldKey"] = "crm.deal.update?$queryParams";
        }

        try {
            $response = Http::post($url, $batchData);
            $batchResponse = $response->json(); // Обработка ответа
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::channel('telegram')->error('APRIL_ONLINE', [
                'updateDealBitrixDealUpdate' => [

                    'RequestException' => $e->getMessage(),



                ]
            ]);


            return [
                'batchResponse' => $batchResponse,
            ];
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::channel('telegram')->error('APRIL_ONLINE', [
                'updateDealBitrixDealUpdate' => [

                    'ConnectionException' => $e->getMessage(),



                ]
            ]);

            return [
                'batchResponse' => $batchResponse,
            ];
        } catch (\Exception $e) {
            Log::channel('telegram')->error('APRIL_ONLINE', [
                'General Exception' => [

                    'ConnectionException' => $e->getMessage(),



                ]
            ]);
            return [
                'batchResponse' => $batchResponse,
            ];
        }

        return [
            'batchResponse' => $batchResponse,
        ];
    }

    protected function productsSet()
    {
        $method = '/crm.item.productrow.set.json';
        $url = $this->hook . $method;
        $this->setProductRowsData['ownerId'] = $this->dealId;
        foreach ($this->setProductRowsData['productRows'] as $product) {
            $product['ownerId'] = $this->dealId;
        }

        try {
            $response = Http::get($url, $this->setProductRowsData);


            return BitrixController::getBitrixRespone($response, 'productsSet');
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];
            Log::channel('telegram')->error('APRIL_ONLINE', [
                'BitrixController Get Hook URL' => $errorMessages
            ]);

            return null;
        }
    }


    protected function productsUpdate()
    {
        $method = '/crm.item.productrow.update.json';
        $url = $this->hook . $method;
        $response = Http::get($url, [
            //todo 
        ]);
        return BitrixController::getBitrixRespone($response, 'productsUpdate');
    }



    // protected function getBitrixRespone($bitrixResponse)
    // {
    //     $response =  $bitrixResponse->json();
    //     if ($response) {
    //         if (isset($response['result'])) {

    //             Log::info('success btrx response', [
    //                 'BTRX_RESPONSE_SUCCESS' => [
    //                     'domain' => $this->domain,
    //                     'result' => $response['result'],

    //                 ]

    //             ]);
    //             return $response['result'];
    //         } else {
    //             if (isset($response['error_description'])) {
    //                 Log::info('error', [
    //                     'SET_DEAL' => [
    //                         'domain' => $this->domain,
    //                         'btrx error' => $response['error'],
    //                         'btrx response' => $response['error_description']
    //                     ]

    //                 ]);
    //                 return null;
    //             }
    //         }
    //     }
    // }
}
