<?php

namespace App\Services;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use morphos\Russian\MoneySpeller;
use morphos\Russian\TimeSpeller;
use Ramsey\Uuid\Uuid;

class BitrixDealDocumentService
{
    protected $domain;
    protected $documentNumber;
    protected $data;
    protected $headerData;
    protected $doubleHeaderData;
    protected $footerData;
    protected $letterData;
    protected $infoblocksData;
    protected $pricesData;
    protected $stampsData;
    protected $isTwoLogo;
    protected $isGeneralInvoice;
    protected $isAlternativeInvoices;
    protected $dealId;

    public function __construct(
        $domain,
        $documentNumber,
        $data,
        $headerData,
        $doubleHeaderData,
        $footerData,
        $letterData,
        $infoblocksData,
        $pricesData,
        $stampsData,
        $isTwoLogo,
        $isGeneralInvoice,
        $isAlternativeInvoices,
        $dealId,



    ) {
        $this->domain =  $domain;
        $this->documentNumber = $documentNumber;
        $this->data = $data;
        $this->headerData =  $headerData;
        $this->doubleHeaderData = $doubleHeaderData;
        $this->footerData = $footerData;
        $this->letterData =  $letterData;
        $this->infoblocksData =  $infoblocksData;
        $this->pricesData =  $pricesData;
        $this->stampsData =  $stampsData;
        $this->isTwoLogo =  $isTwoLogo;

        $this->isGeneralInvoice =  $isGeneralInvoice;
        $this->isAlternativeInvoices =  $isAlternativeInvoices;

        $this->dealId =  $dealId;
    }


    public function getDocuments()
    {

        $data = $this->data;
        $result = [
            'offerLink' => '',
            'invoiceLinks' => [],
            'links' => [],
        ];

        $offerLink = $this->createDocumentOffer();
        $links = [];
        $invoices = [];


        array_push($links, $offerLink);
        if ($this->isGeneralInvoice) {
            $generalInvoice = $this->createDocumentInvoice();
            array_push($invoices, $generalInvoice);
            array_push($links, $generalInvoice);
        }
        if ($this->isAlternativeInvoices) {
            foreach ($data['price']['cells']['alternative'] as $key => $product) {

                $alternativeInvoice = $this->createDocumentInvoice(false, $key);

                array_push($invoices, $alternativeInvoice);
                array_push($links, $alternativeInvoice);
            }
        }

        $bitrixDealUpdateResponse = $this->updateDeal($links);
        $result = [
            'offerLink' => $offerLink,
            'invoiceLinks' => $invoices,
            'links' => $links,
            'bitrixDealUpdateResponse' => $bitrixDealUpdateResponse,
            // 'qr_path' => $data['provider']['rq']['qrs'][0]['path']
        ];

        return  $result;
    }
    protected function createDocumentOffer()
    {



        try {
            $pdf = Pdf::loadView('pdf.offer', [
                'headerData' =>  $this->headerData,
                'doubleHeaderData' =>  $this->doubleHeaderData,
                'footerData' =>  $this->footerData,
                'letterData' => $this->letterData,
                'infoblocksData' => $this->infoblocksData,
                'pricesData' => $this->pricesData,
                'stampsData' => $this->stampsData,
                // 'invoiceData' => $invoiceData,
            ]);


            $data = $this->data;
            $domain = $this->domain;
            $documentNumber = $this->documentNumber;


            // //СОХРАНЕНИЕ ДОКУМЕТА
            $uid = Uuid::uuid4()->toString();
            $shortUid = substr($uid, 0, 4); // Получение первых 4 символов

            $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/' . $data['userId']);


            if (!file_exists($resultPath)) {
                mkdir($resultPath, 0775, true); // Создать каталог с правами доступа
            }

            // Проверить доступность каталога для записи
            if (!is_writable($resultPath)) {
                throw new \Exception("Невозможно записать в каталог: $resultPath");
            }
            $resultFileName = $documentNumber . '_' . $shortUid . '.pdf';
            $pdf->save($resultPath . '/' . $resultFileName);

            // $objWriter->save($resultPath . '/' . $resultFileName);

            // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ

            $offerLink = asset('storage/clients/' . $domain . '/documents/' . $data['userId'] . '/' . $resultFileName);
            return $offerLink;
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];
            Log::error('ERROR: Exception caught',  $errorMessages);
            Log::info('error', ['error' => $th->getMessage()]);
            return null;
        }
    }
    protected function createDocumentInvoice($isGeneral = true, $alternativeSetId = 0)
    {



        try {
            $data = $this->data;
            $documentNumber = $this->documentNumber;
            $headerData  = $this->headerData;
            $doubleHeaderData  = $this->doubleHeaderData;
            $stampsData  =   $this->getStampsData(true);

            if ($data &&  isset($data['template'])) {
                $template = $data['template'];
                if ($template && isset($template['id'])) {


                    $templateId = $template['id'];
                    $domain = $data['template']['portal'];
                    $dealId = $data['dealId'];
                    $providerRq = $data['provider']['rq'];





                    //price
                    $price = $data['price'];
                    $recipient = $data['recipient'];


                    //invoice
                    $invoiceBaseNumber =  preg_replace('/\D/', '', $documentNumber);
                    if (!$isGeneral) {
                        $invoiceBaseNumber = $invoiceBaseNumber . '-' . $alternativeSetId + 1;
                    }

                    $comePrices = $price['cells'];




                    //document data

                    $invoiceData  =   $this->getInvoiceData($invoiceBaseNumber, $providerRq, $recipient, $price, $isGeneral, $alternativeSetId);

                    // ГЕНЕРАЦИЯ ДОКУМЕНТА СЧЕТ
                    $pdf = Pdf::loadView('pdf.invoice', [
                        'headerData' =>  $headerData,
                        'doubleHeaderData' =>  $doubleHeaderData,
                        'stampsData' => $stampsData,
                        'invoiceData' => $invoiceData,
                    ]);



                    // // //СОХРАНЕНИЕ ДОКУМЕТА
                    $uid = Uuid::uuid4()->toString();
                    $shortUid = substr($uid, 0, 4); // Получение первых 4 символов

                    $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/' . $data['userId']);
                    $invoicePath = $resultPath . '/invoice';

                    if (!file_exists($invoicePath)) {
                        mkdir($invoicePath, 0775, true); // Создать каталог с правами доступа
                    }

                    // Проверить доступность каталога для записи
                    if (!is_writable($invoicePath)) {
                        throw new \Exception("Невозможно записать в каталог: $invoicePath");
                    }
                    $resultFileName = 'Счет-' . $invoiceBaseNumber . '_' . $shortUid . '.pdf';
                    $pdf->save($invoicePath . '/' . $resultFileName);
                    $link = asset('storage/clients/' . $domain . '/documents/' . $data['userId'] . '/invoice/' . $resultFileName);


                    return  $link;
                }
            }
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];
            Log::error('ERROR: Exception caught',  $errorMessages);
            Log::info('error', ['error' => $th->getMessage()]);
            return APIController::getError($th->getMessage(),  $errorMessages);
        }
    }

    protected function getInvoiceData(
        $invoiceBaseNumber,
        $providerRq,
        $recipient,
        $price,
        $isGeneral,
        $alternativeSetId,

    ) {
        $pricesData  =   $this->getInvoicePricesData($price, $isGeneral, $alternativeSetId);
        $date = $this->getToday();
        $invoiceNumber = 'Счет на оплату № ' . $invoiceBaseNumber . ' от ' .  $date;
        $withQr = false;
        $qr = null;
        if (isset($providerRq['qrs'])) {
            if (count($providerRq['qrs'])) {
                if (isset($providerRq['qrs'][0])) {

                    if (isset($providerRq['qrs'][0]['path'])) {

                        $withQr = true;

                        $qr = storage_path('app/' .  $providerRq['qrs'][0]['path']);;
                    }
                }
            }
        }
        $req = $providerRq;
        $req['withQr'] = $withQr;
        $req['qr'] = $qr;
        $invoiceData = [
            // 'stampsData' => $stampsData,
            'rq' => $req,

            'main' => [
                'rq' => $providerRq,
                'recipient' => $recipient,
                'number' => $invoiceNumber,
            ],


            'pricesData' => $pricesData,

        ];

        return $invoiceData;
    }

    protected function getStampsData($isInvoice)
    {
        $providerRq = $this->data['provider']['rq'];;
        $stampsData = [
            'position' => '',
            'stamp' => '',
            'signature' => '',
            'signature_accountant' => '',
            'director' => '',
            'accountant' => '',
            'isInvoice' => $isInvoice
        ];
        $stamps = $providerRq['stamps'];
        $signatures = $providerRq['signatures'];

        if (!empty($stamps)) {
            $stampsData['stamp'] = storage_path('app/' . $stamps[0]['path']);
        }
        if (!empty($signatures)) {
            foreach ($signatures as $key => $signature) {
                if ($signature['code'] !== 'signature_accountant') {
                    $stampsData['signature'] = storage_path('app/' . $signature['path']);
                } else {
                    $stampsData['signature_accountant'] = storage_path('app/' . $signature['path']);
                }
            }
        }



        $stampsData['position'] = $providerRq['position'] . ' ' . $providerRq['fullname'];
        if ($providerRq['type'] == 'ip') {
            $stampsData['position'] = $providerRq['fullname'];
        }



        if ($providerRq['type'] == 'org') {
            $stampsData['director']  = $providerRq['director'];
        }


        $stampsData['accountant'] = $providerRq['accountant'];




        return $stampsData;
    }


    protected function getInvoicePricesData($price, $isGeneral = true, $alternativeSetId)
    {
        $isTable = $price['isTable'];
        $comePrices = $price['cells'];
        $total = '';
        $fullTotalstring = '';
        $totalSum = 0;        //SORT CELLS
        $sortActivePrices = $this->getSortActivePrices($comePrices, true);
        $allPrices =  $sortActivePrices['general'];



        $quantityMeasureString = '';
        $quantityString = '';
        $measureString = '';
        $contract = null;


        foreach ($price['cells']['total'][0]['cells'] as $contractCell) {
            if ($contractCell['code'] === 'contract') {
                $contract = $contractCell['value'];
            }
        }

        $foundCell = null;

        foreach ($price['cells']['total'][0]['cells'] as $cell) {
            if ($cell['code'] === 'prepaymentsum') {
                $foundCell = $cell;
            }
        }


        $targetProducts = $sortActivePrices['general'];
        $totalCells = $price['cells']['total'][0]['cells'];


        if (!$isGeneral) {
            foreach ($price['cells']['alternative'][$alternativeSetId]['cells'] as $contractCell) {
                if ($contractCell['code'] === 'contract') {
                    $contract = $contractCell['value'];
                }
            }
            $targetProducts = $sortActivePrices['alternative'];
            $totalCells = $price['cells']['alternative'][$alternativeSetId]['cells'];
            $allPrices = [$sortActivePrices['alternative'][$alternativeSetId]];


            foreach ($price['cells']['alternative'][$alternativeSetId]['cells'] as $contractCell) {
                if ($contractCell['code'] === 'contract') {
                    $contract = $contractCell['value'];
                }
            }

            $foundCell = null;

            foreach ($price['cells']['alternative'][$alternativeSetId]['cells'] as $cell) {
                if ($cell['code'] === 'prepaymentsum') {
                    $foundCell = $cell;
                }
            }
        }


        $allProductForInvoice = [];
        foreach ($targetProducts as $productKey => $product) {
            if ($isGeneral) {
                array_push($allProductForInvoice, $product);
            } else {
                if ($productKey == $alternativeSetId) {
                    array_push($allProductForInvoice, $product);
                }
            }
        }




        foreach ($totalCells  as $cell) {

            if ($cell['code'] === 'quantity' && $cell) {
                // if ($contract['shortName'] !== 'internet' && $contract['shortName'] !== 'proxima') {

                //     // $qcount = $contract['prepayment'] * $cell['value'];
                //     Log::error('CONTRACT: ', ['contract' => $contract]);
                //     Log::error('CONTRACT: ', ['contract-prepayment' => $contract['prepayment']]);

                //     Log::error('cell: ', ['cell' => $cell]);
                //     Log::error('cell-value: ', ['cell-value' => $cell['value']]);


                //     // $qcount =    (float)$contract['prepayment'] * (float)$cell['value'];
                //     $qcount =    (float)$cell['value'];

                //     $quantityString =  TimeSpeller::spellUnit($qcount, TimeSpeller::MONTH);
                // } else {
                    // $qcount =    (float)$cell['value'];
                    $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT);
                    
                    // Преобразуем результат в число
                    $quantity = intval($numberString);
                    $quantityString = TimeSpeller::spellUnit($quantity, TimeSpeller::MONTH);
                // }
            }

            if ($cell['code'] === 'measure' && $cell['value']) {
                if ($cell['isActive']) {
                    // foreach ($price['cells']['total'][0]['cells'] as $contractCell) {
                    //     if ($contractCell['code'] === 'contract') {
                    //         $measureString = $contractCell['value']['measureFullName'];
                    //     }
                    // }

                }
            }
        }

        $quantityMeasureString = '\n За ' . '<color>' . $quantityString . '</color>';


        if ($foundCell) {
            $totalSum = $foundCell['value'];
            $totalSum = MoneySpeller::spell($totalSum, MoneySpeller::RUBLE, MoneySpeller::SHORT_FORMAT);
            $total = '<color>' . $totalSum . '</color> ';
        }

        $result = MoneySpeller::spell($foundCell['value'], MoneySpeller::RUBLE);
        $firstChar = mb_strtoupper(mb_substr($result, 0, 1, "UTF-8"), "UTF-8");
        $restOfText = mb_substr($result, 1, mb_strlen($result, "UTF-8"), "UTF-8");






        $text = ' (' . $firstChar . $restOfText . ') без НДС';
        $textTotalSum = $text;

        $fullTotalstring = $total . ' ' . $textTotalSum . $quantityMeasureString;


        return [
            'isTable' => $isTable,
            // 'isInvoice' => $isInvoice,
            'allPrices' => $allPrices,
            // 'withPrice' => $withPrice,
            'withTotal' => true,
            'total' => $fullTotalstring

        ];
    }

    protected function getSortActivePrices($allPrices,  $isInvoice)
    {

        $result = [
            'general' => [],
            'alternative' => [],
            'total' => []
        ];
        foreach ($allPrices as $key => $target) {

            if ($target) {

                if (is_array($target) && !empty($target)) {
                    $result[$key] = $target;
                    foreach ($target as $index => $product) {

                        if ($product) {

                            if (
                                is_array($product) && !empty($product) && is_array($product['cells']) && !empty($product['cells'])
                            ) {
                                if (!$isInvoice) {
                                    $filtredCells = array_filter($product['cells'], function ($prc) {
                                        return $prc['isActive'] == true;
                                    });
                                    usort($filtredCells, function ($a, $b) {
                                        return $a['order'] - $b['order'];
                                    });
                                } else {
                                    $filtredCells = [];
                                    usort($product['cells'], function ($a, $b) {
                                        return $a['order'] - $b['order'];
                                    });
                                    foreach ($product['cells'] as $cell) {
                                        $searchingCell = null;

                                        if ($cell['code'] === 'name') {
                                            $searchingCell = $cell;
                                        }

                                        if ($cell['code'] === 'current') {
                                            $searchingCell = $cell;
                                        }
                                        if ($cell['code'] === 'quantity') {
                                            $cell['name'] = 'Кол-во';
                                            if (preg_match('/\d+/', (string)$cell['value'], $matches)) {
                                                $cell['value'] = $matches[0];
                                            }
                                            $searchingCell = $cell;
                                        }
                                        if ($cell['code'] === 'measure') {
                                            $searchingCell = $cell;
                                        }
                                        if ($cell['code'] === 'prepaymentsum') {
                                            $cell['name'] = 'Сумма';
                                            $searchingCell = $cell;
                                        }

                                        if ($searchingCell) {
                                            array_push($filtredCells, $searchingCell);
                                        }
                                    }
                                    // $filtredCells = array_filter($product['cells'], function ($prc) {
                                    //     return $prc['isActive'] == true || $prc['code'] == 'measure';
                                    // });
                                }
                                $result[$key][$index]['cells']  = $filtredCells;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }


    protected function getToday()
    {
        $months = [
            1 => 'января',
            2 => 'февраля',
            3 => 'марта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря'
        ];

        // Получаем текущую дату
        $currentDate = getdate();

        // Форматируем дату
        $formattedDate = $currentDate['mday'] . ' ' . $months[$currentDate['mon']] . ' ' . $currentDate['year'];

        return $formattedDate;
    }







    public function updateDeal($links)
    {


        //change stage
        //if only offer - offer
        //if  with Invoice - Invoice


        try {
            $bitrixController = new BitrixController();
            $responseChangeDeal = $bitrixController->changeDealStage($this->domain, $this->dealId, "offer");
            if (count($links) > 1) {
                $responseChangeDeal = $bitrixController->changeDealStage($this->domain, $this->dealId, "invoice");
            }


            $responseTimeLine = $this->setTimeline($links);
            return [
                'responseChangeDeal' => $responseChangeDeal,
                'responseTimeLine' => $responseTimeLine,

            ];
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];
            Log::error('ERROR: Exception caught',  $errorMessages);
            Log::info('error', ['error' => $th->getMessage()]);
            return APIController::getError($th->getMessage(),  $errorMessages);
        }
    }

    public function setDocument()
    {



        try {
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];
            Log::error('ERROR: Exception caught',  $errorMessages);
            Log::info('error', ['error' => $th->getMessage()]);
            return APIController::getError($th->getMessage(),  $errorMessages);
        }
    }


    public function setTimeline($links)
    {

        $domain = $this->domain;
        $dealId = $this->dealId;
        $commentText = $this->documentNumber;
        $method = '/crm.timeline.comment.add';
        // $bitrixController = new BitrixController();
        // $resultTex = "<a href=\\" . $commentLink . "\>" . $commentText . "</a>";

        $resultText = '';
        $currentCommentText = $commentText;
        foreach ($links as $key => $commentLink) {
            if ($key) {
                if ($key == 1) {
                    $currentCommentText = 'СЧЕТ-' . $commentText;
                } else {
                    $numb = $key  - 1;
                    $currentCommentText = 'СЧЕТ-' . $commentText . '-' . $numb;
                }
            }
            $resultText = $resultText . "<a href=\"" . htmlspecialchars($commentLink) . "\">" . htmlspecialchars($currentCommentText) . "</a> \n";
        }
        try {
            $hook = BitrixController::getHook($domain); // Предполагаем, что функция getHookUrl уже определена


            $url = $hook . $method;
            $fields = [
                "ENTITY_ID" => $dealId,
                "ENTITY_TYPE" => "deal",
                "COMMENT" => $resultText
            ];
            $data = [
                'fields' => $fields
            ];
            $responseBitrix = Http::get($url, $data);
            $response =  $responseBitrix->json();
            if ($response) {
                if (isset($response['result'])) {
                    return $response['result'];
                } else {
                    if (isset($response['error_description'])) {
                        return $response['error_description'];
                    }
                }
            }
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['data' => [$domain, $dealId, $commentLink, $commentText]]);
        }
    }
}
