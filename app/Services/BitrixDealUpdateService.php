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
    protected $updateDealData;
    protected $setProductRowsData;
    protected $updateProductRowsData;


    public function __construct(
        $domain,
        $dealId,
        $setDealData,
        $updateDealData,
        $setProductRowsData,
        $updateProductRowsData




    ) {
        $this->domain =  $domain;
        $this->dealId =  $dealId;
        $hook = BitrixController::getHook($domain);
        $this->hook =  $hook;

        $this->setDealData =  $setDealData;
        $this->updateDealData =  $updateDealData;


        $this->setProductRowsData =  $setProductRowsData;
        $this->updateProductRowsData =  $updateProductRowsData;
    }


    public function dealProccess()
    {
        if (!$this->dealId) {
            $newDeal = $this->setDeal();
            $this->dealId = $newDeal['id'];
        }
        $updatedDeal = $this->updateDeal();

        $result = [
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
        $method = '/crm.deal.update.json';
        $url = $this->hook . $method;
        $this->updateDealData['ID'] = $this->dealId;
        Log::info('error', [
            'UPDATE_DEAL' => [
                'domain' => $this->domain,
                'data' =>  $this->updateDealData,
             
            ]

        ]);
        $response = Http::get($url, $this->updateDealData);
        return $this->getBitrixRespone($response);
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
