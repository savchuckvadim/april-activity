<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CounterController;
use App\Services\Document\DocumentOfferInvoiceService;

class OfferController extends Controller
{

    public function getDocument($data)
    {
        try {
            //code...

            if ($data &&  isset($data['template']) && isset($data['infoblocks'])) {
                $template = $data['template'];
                if ($template && isset($template['id'])) {

                    $withHook = false;
                    $templateId = $template['id'];
                    $domain = $data['template']['portal'];
                    $dealId = $data['dealId'];
                    $providerRq = $data['provider']['rq'];
                    $isTwoLogo = false;
                    if ($providerRq) {
                        if (isset($providerRq['logos'])) {
                            if (count($providerRq['logos']) > 1) {
                                $isTwoLogo = true;
                            }
                        }
                    }

                    if (isset($data['withHook'])) {
                        if (!empty($data['withHook'])) {
                            $withHook = $data['withHook'];
                        }
                    }




                    //TODO BIG DESCRIPTION

                    $placement = null;
                    if (isset($data['placement'])) {
                        $placement = $data['placement'];
                    }

                    $userId = null;
                    if (isset($data['userId'])) {
                        $userId = $data['userId'];
                    }


                    //DOCUMENT SETTINGS
                    //infoblocks data
                    $infoblocksOptions = [
                        'description' => $data['infoblocks']['description']['current'],
                        // 'description' => ['id' => 3],
                        'style' => $data['infoblocks']['style']['current']['code'],
                    ];

                    $settings = $data['infoblocks'];
                    $withStamps = true;
                    $withManager = true;


                    if (isset($settings['withStamps'])) {
                        if (isset($settings['withStamps']['current'])) {
                            if (isset($settings['withStamps']['current']['code'])) {
                                if ($settings['withStamps']['current']['code'] === 'yes') {
                                    $withStamps = true;
                                } else if ($settings['withStamps']['current']['code'] === 'no') {
                                    $withStamps = false;
                                }
                            }
                        }
                    } else {
                        if (isset($data['withStamps'])) {
                            $withStamps = $data['withStamps'];
                        }
                    }
                    if (isset($settings['withManager'])) {
                        if (isset($settings['withManager']['current'])) {
                            if (isset($settings['withManager']['current']['code'])) {
                                if ($settings['withManager']['current']['code'] === 'yes') {
                                    $withManager = true;
                                } else if ($settings['withManager']['current']['code'] === 'no') {
                                    $withManager = false;
                                }
                            }
                        }
                    }
                    $salePhrase = '';

                    if (isset($data['salePhrase'])) {
                        $salePhrase = $data['salePhrase'];
                    }

                    $priceFirst = false;
                    if (!empty($data['settings'])) {
                        if (!empty($data['settings']['isPriceFirst'])) {
                            if (!empty($data['settings']['isPriceFirst']['current'])) {
                                if (!empty($data['settings']['isPriceFirst']['current']['id'])) {
                                    $priceFirst = true; //0 - no 1 -yes
                                }
                            }
                        }
                    }

                    $complect = $data['complect'];

                    $region = $data['region'];

                    $complectName = '';

                    foreach ($data['price']['cells']['general'][0]['cells'] as $cell) {

                        if ($cell['code'] === 'name') {
                            $complectName = $cell['value'];
                        }
                    }



                    //price
                    $price = $data['price'];


                    //manager
                    $manager = $data['manager'];
                    //UF_DEPARTMENT
                    //SECOND_NAME


                    //fields
                    $fields = $data['template']['fields'];
                    $recipient = $data['recipient'];


                    //document number
                    $documentNumber = CounterController::getCount($providerRq['id'], 'offer');

                    //invoice
                    $invoices = [];
                    $invoiceBaseNumber =  preg_replace('/\D/', '', $documentNumber);
                    $invoiceData = $data['invoice'];
                    $invoiceDate = '';

                    if (isset($data['invoiceDate'])) {
                        $invoiceDate = $data['invoiceDate'];
                    }


                    $isGeneralInvoice = false;
                    $isAlternativeInvoices = false;



                    if (isset($invoiceData['one']) && isset($invoiceData['many'])) {
                        $isGeneralInvoice = $invoiceData['one']['isActive'] && $invoiceData['one']['value'];
                        $isAlternativeInvoices = $invoiceData['many']['isActive'] && $invoiceData['many']['value'];
                    }



                    //general
                    $comePrices = $price['cells'];
                    $productsCount = $this->getProductsCount($comePrices);




                    $regions = null;
                    if (isset($data['regions'])) {
                        $regions = $data['regions'];
                    }

                    $result = DocumentOfferInvoiceService::getDocument(
                        $infoblocksOptions,
                        $complectName,
                        $productsCount,
                        $region,
                        $salePhrase,
                        $withStamps,
                        $priceFirst,
                        $regions,
                        '$contract',
                        'offer',
                        $complect,
                
                
                
                        $domain,
                        $providerRq,
                        $isTwoLogo,
                        $manager,
                        $documentNumber,
                        $fields, //template fields
                        $recipient,
                
                
                        $price,
                        '$alternativeSetId'
                    );


                    return APIController::getSuccess($result);
                }
            }
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['data' => $data]);
        }
    }

    protected function getProductsCount(
        $allPrices

    ) {

        $result = 0;
        $alternative =  $allPrices['alternative'];
        $general =  $allPrices['general'];



        if (is_array($alternative) && is_array($general)) {

            if (count($general) > 0) {  //если больше одного основного товара
                $result = $result + count($general);
            }
            if (count($alternative) > 0) {  //если больше одного  товара
                $result = $result + count($alternative);
            }
        }

        return $result;
    }
}
