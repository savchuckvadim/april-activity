<?php

namespace App\Services;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\PortalController;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use morphos\Russian\MoneySpeller;
use morphos\Russian\TimeSpeller;
use Ramsey\Uuid\Uuid;

class BitrixDealDocumentService
{
    protected $domain;
    protected $placement;
    protected $placementType;
    protected $currentEntityId;

    protected $hook;
    protected $providerRq;
    protected $documentNumber;

    protected $documentInvoiceNumber;
    protected $data;
    protected $invoiceDate;

    protected $headerData;
    protected $doubleHeaderData;
    protected $footerData;
    protected $letterData;
    protected $infoblocksData;
    protected $bigDescriptionData;
    protected $pricesData;
    protected $stampsData;
    protected $isTwoLogo;
    protected $isGeneralInvoice;
    protected $isAlternativeInvoices;


    protected $withStamps;
    protected $userId;
    protected $aprilSmartData;


    protected $categoryId = 26;           //целевая воронка смарт
    protected $stageId = 'DT162_26:UC_R7UBSZ';         //целевая стадия offer april
    protected $smartCrmId = 'T9c_';                      // из бд типа с 'T9c_'
    protected $smartEntityTypeId;

    protected $leadId;
    protected $companyId;
    protected $dealId;

    public function __construct(
        $domain,
        $placement,
        $userId,
        $providerRq,
        $documentNumber,
        $data,
        $invoiceDate,
        $headerData,
        $doubleHeaderData,
        $footerData,
        $letterData,
        $infoblocksData,
        $bigDescriptionData,
        $pricesData,
        $stampsData,
        $isTwoLogo,
        $isGeneralInvoice,
        $isAlternativeInvoices,
        $dealId,
        $withStamps



    ) {
        $this->domain =  $domain;
        $this->placement =  $placement;
        $this->userId =  $userId;
        $this->providerRq =  $providerRq;
        $this->documentNumber = $documentNumber;
        $this->data = $data;
        $this->invoiceDate = $invoiceDate;

        $this->headerData =  $headerData;
        $this->doubleHeaderData = $doubleHeaderData;
        $this->footerData = $footerData;
        $this->letterData =  $letterData;
        $this->infoblocksData =  $infoblocksData;
        $this->bigDescriptionData =  $bigDescriptionData;
        $this->pricesData =  $pricesData;
        $this->stampsData =  $stampsData;
        $this->isTwoLogo =  $isTwoLogo;

        $this->isGeneralInvoice =  $isGeneralInvoice;
        $this->isAlternativeInvoices =  $isAlternativeInvoices;

        $this->dealId =  $dealId;
        $this->withStamps = $withStamps;
        $this->hook = BitrixController::getHook($domain);

        $this->placementType = null;
        $this->currentEntityId = null;


        if ($placement && isset($placement['placement'])) {
            // placement":{
            //     "placement":"CRM_COMPANY_DETAIL_TAB",
            //     "options":{"ID":"11708"}
            // }
            $str = $placement['placement'];

            // Проверка на наличие подстроки LEAD
            if (strpos($str, "LEAD") !== false) {
                $this->placementType = "LEAD";
                if (isset($placement['options'])) {
                    $this->leadId  = $placement['options']['ID'];
                }
            } else if (strpos($str, "COMPANY") !== false) {

                $this->placementType = "COMPANY";
                if (isset($placement['options'])) {
                    $this->companyId  = $placement['options']['ID'];
                }
            } else if (strpos($str, "DEAL") !== false) {

                $this->placementType = "DEAL";
                $deal = $this->getDeal($this->dealId);
                if ($deal) {
                    if (!empty($deal['COMPANY_ID'])) {

                        $this->companyId = $deal['COMPANY_ID'];
                    } else  if (!empty($deal['LEAD_ID'])) {
                        $this->leadId  =  $deal['COMPANY_ID'];
                    }
                }
            }

            if (isset($placement['options'])) {
                $this->currentEntityId = $placement['options']['ID'];
            }
        }






        $portal = null;
        $portalResponse = PortalController::innerGetPortal($domain);
        if ($portalResponse && !empty($portalResponse['portal'])) {
            $portal = $portalResponse['portal'];

            if (!empty($portal['bitrixSmart'])) {
                $this->aprilSmartData = $portal['bitrixSmart'];

                $smartId = 'T9c_';
                if (isset($portal['bitrixSmart']['crm'])) {
                    $smartId =  $portal['bitrixSmart']['crm'] . '_';
                }

                $this->smartCrmId =  $smartId;
            }
        }



        // DT162_26:UC_R7UBSZ	КП/Счет  april

        //DT156_12:UC_FA778R	КП сформировано //alfacenter
        //DT156_12:UC_I0J7WW	Счет сформирован //alfacenter

        if ($domain == 'alfacentr.bitrix24.ru') {

            $this->categoryId = 12;

            if ($isGeneralInvoice) {         //счет
                $this->stageId = 'DT156_12:UC_I0J7WW';   //DT156_12:UC_I0J7WW	Счет сформирован //alfacenter
            } else {                            //кп
                $this->stageId = 'DT156_12:UC_FA778R'; // КП сформировано //alfacenter

            }
        }
    }


    public function getDocuments()
    {

        $data = $this->data;
        $result = [
            'offerLink' => '',
            'link' => '',
            'invoiceLinks' => [],
            'links' => [],
        ];

        $offerLink = $this->createDocumentOffer();
        $links = [];
        $invoices = [];


        array_push($links, $offerLink);
        if ($this->isGeneralInvoice) {
            $this->documentInvoiceNumber = CounterController::getCount($this->providerRq['id'], 'invoice');
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

        //testing
        $bitrixDealUpdateResponse = $this->updateDeal($links);
        $this->smartProccess();
        $result = [
            'offerLink' => $offerLink,
            'link' => $offerLink,
            // 'link' => $invoices[0],  invoice testing
            'invoiceLinks' => $invoices,
            'links' => $links,
            'bigDescriptionData' => $this->bigDescriptionData

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
                'bigDescriptionData' => $this->bigDescriptionData,
                'pricesData' => $this->pricesData,
                'stampsData' =>  $this->stampsData,
                'withStamps' =>   $this->withStamps
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
            $documentNumber = $this->documentInvoiceNumber;
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
                        'withStamps' =>   $this->withStamps


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
        $prettyDate = '';
        if ($this->invoiceDate) {
            $prettyDate = date("d.m.Y", strtotime($this->invoiceDate)) . ' г.';
        }
        $req = $providerRq;
        $req['withQr'] = $withQr;
        $req['qr'] = $qr;

        $pattern = "/общество\s+с\s+ограниченной\s+ответственностью/ui";
        $shortenedPhrase = preg_replace($pattern, "ООО", $providerRq['fullname']);
        $companyName = preg_replace($pattern, "ООО", $providerRq['fullname']);






        if ($providerRq['type'] == 'org') {
            $req['fullname'] = $companyName;
        }
        $invoiceData = [
            // 'stampsData' => $stampsData,
            'rq' => $req,

            'main' => [
                'rq' => $providerRq,
                'recipient' => $recipient,
                'number' => $invoiceNumber,
                'invoiceDate' => $prettyDate
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

        $pattern = "/общество\s+с\s+ограниченной\s+ответственностью/ui";
        $shortenedPhrase = preg_replace($pattern, "ООО", $providerRq['fullname']);
        $companyName = preg_replace($pattern, "ООО", $providerRq['fullname']);



        $stampsData['position'] = $providerRq['position'] . ' ' . $companyName;


        if ($providerRq['type'] == 'ip') {
            $stampsData['position'] = $providerRq['fullname'];
        }



        if ($providerRq['type'] == 'org') {
            $stampsData['director']  = $this->getShortName($providerRq['director']);
        }


        $stampsData['accountant'] = $this->getShortName($providerRq['accountant']);




        return $stampsData;
    }


    protected function getShortName($fullName)
    {
        // $fullName = "Иванов Петр Сергеевич";

        // Разделяем полное имя на части
        $parts = explode(' ', $fullName);

        // Проверяем, что имя содержит три части: фамилию, имя, отчество
        if (count($parts) === 3) {
            $shortName = $parts[0] . ' ' . mb_substr($parts[1], 0, 1) . '. ' . mb_substr($parts[2], 0, 1) . '. ';
        } else {
            // Если формат имени отличается, вернуть оригинальное имя или обработать иначе
            $shortName = $fullName;
        }

        return $shortName; // Вывод: Иванов П.С.
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
                if ($cell['code'] === 'quantity' && $cell['value']) {
                    if ($contract['shortName'] !== 'internet' && $contract['shortName'] !== 'proxima') {

                        $qcount =  (float)$contract['prepayment'] * (float)$cell['defaultValue'];
                        $quantity = intval($qcount);
                        $quantityString =  TimeSpeller::spellUnit($quantity, TimeSpeller::MONTH);
                    } else {
                        $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT);

                        // Преобразуем результат в число
                        $quantity = intval($numberString);
                        $quantityString = TimeSpeller::spellUnit($quantity, TimeSpeller::MONTH);
                    }
                }
                // $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT); //чистое количество

                // $quantity = intval($numberString);                                          //преобразует строку в число
                // $qcount =    (float)$contract['prepayment'] * (float)$cell['value'];
                // $quantityString = TimeSpeller::spellUnit($qcount, TimeSpeller::MONTH);

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
        $currentCommentText = 'Коммерческое предложение-' . $commentText;
        foreach ($links as $key => $commentLink) {
            if ($key) {
                $commentText = $this->documentInvoiceNumber;
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
            $hook = $this->hook; // Предполагаем, что функция getHookUrl уже определена


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


    protected function smartProccess()
    {
        sleep(1);
        $currentSmart = $this->getSmartItem();

        sleep(1);
        if ($currentSmart) {
            if (isset($currentSmart['id'])) {
                $currentSmart = $this->updateSmartItem($currentSmart['id']);
            }
        } else {
            $currentSmart = $this->createSmartItem();
            // $currentSmart = $this->updateSmartItemCold($currentSmart['id']);
        }


        Log::channel('telegram')->info('APRIL_ONLINE', [
            'BitrixDealDocumentService: ' => [

                'currentSmart' => $currentSmart,


            ]
        ]);
    }


    protected function getSmartItem()
    {
        // lidIds UF_CRM_7_1697129081
        $leadId  = $this->leadId;

        $companyId = $this->companyId;
        $userId = $this->userId;
        $smart = $this->aprilSmartData;


        $currentSmart = null;

        $method = '/crm.item.list.json';
        $url = $this->hook . $method;
        if ($companyId) {
            $data =  [
                'entityTypeId' => $smart['crmId'],
                'filter' => [
                    "!=stage_id" => ["DT162_26:SUCCESS", "DT156_12:SUCCESS"],
                    "=assignedById" => $userId,
                    'COMPANY_ID' => $companyId,

                ],
                // 'select' => ["ID"],
            ];
        } else if ($leadId) {
            $data =  [
                'entityTypeId' => $smart['crmId'],
                'filter' => [
                    "!=stage_id" => ["DT162_26:SUCCESS", "DT156_12:SUCCESS"],
                    "=assignedById" => $userId,

                    "=%ufCrm7_1697129081" => '%' . $leadId . '%',

                ],
                // 'select' => ["ID"],
            ];
        }



        $response = Http::get($url, $data);
        // $responseData = $response->json();
        $responseData = BitrixController::getBitrixRespone($response, 'BitrixDealDocumentService: getSmartItem');
        if (isset($responseData)) {
            if (!empty($responseData['items'])) {
                $currentSmart =  $responseData['items'][0];
            }
        }

        return $currentSmart;
    }






    //smart
    protected function createSmartItem()
    {

        $methodSmart = '/crm.item.add.json';
        $url = $this->hook . $methodSmart;




        // $hook = $this->hook;
        $companyId  = $this->companyId;
        $responsibleId  = $this->userId;
        $smart  = $this->aprilSmartData;

        $leadId  = $this->leadId;


        $resulFields = [];
        $fieldsData = [];
        $fieldsData['categoryId'] = $this->categoryId;
        $fieldsData['stageId'] = $this->stageId;
        // $fieldsData['ufCrm7_1698134405'] = $companyId;
        $fieldsData['assigned_by_id'] = $responsibleId;
        // $fieldsData['companyId'] = $companyId;

        if ($companyId) {
            $fieldsData['ufCrm7_1698134405'] = $companyId;
            $fieldsData['company_id'] = $companyId;
        }
        if ($leadId) {
            $fieldsData['parentId1'] = $leadId;
            $fieldsData['ufCrm7_1697129037'] = $leadId;
        }




        $entityId = $smart['crmId'];
        $data = [
            'entityTypeId' => $entityId,
            'fields' =>  $fieldsData

        ];
        // Log::info('create Smart Item Cold', [$data]);
        // Возвращение ответа клиенту в формате JSON

        $smartFieldsResponse = Http::get($url, $data);
        // $bitrixResponse = $smartFieldsResponse->json();
        $responseData = BitrixController::getBitrixRespone($smartFieldsResponse, 'BitrixDealDocumentService: createSmartItem');
        // Log::info('COLD createSmartItemCold', ['createSmartItemCold' => $responseData]);
        // $resultFields = null;
        // if (isset($responseData)) {
        $resultFields = $responseData;
        // }
        // Log::channel('telegram')->error('APRIL_HOOK', [
        //     'btrx createSmartItemCold' => $resultFields,

        // ]);

        return $resultFields;
    }

    protected function updateSmartItem($smartId)
    {

        $methodSmart = '/crm.item.update.json';
        $url = $this->hook . $methodSmart;

        //lead
        //leadId UF_CRM_7_1697129037


        $companyId  = $this->companyId;
        $leadId  = $this->leadId;
        $responsibleId  = $this->userId;
        $smart  = $this->aprilSmartData;


        // $resulFields = [];
        $fieldsData = [];

        $fieldsData['categoryId'] = $this->categoryId;
        $fieldsData['stageId'] = $this->stageId;

        // $fieldsData['ufCrm6_1702652862'] = $responsibleId; // alfacenter Ответственный ХО 
        $fieldsData['assigned_by_id'] = $responsibleId;

        if ($companyId) {
            $fieldsData['ufCrm7_1698134405'] = $companyId;
            $fieldsData['company_id'] = $companyId;
        }
        if ($leadId) {
            $fieldsData['parentId1'] = $leadId;
            $fieldsData['ufCrm7_1697129037'] = $leadId;
        }







        $entityId = $smart['crmId'];
        $data = [
            'id' => $smartId,
            'entityTypeId' => $entityId,

            'fields' =>  $fieldsData

        ];


        $smartFieldsResponse = Http::get($url, $data);
        $responseData = BitrixController::getBitrixRespone($smartFieldsResponse, 'cold: updateSmartItemCold');
        $resultFields = $responseData;

        return $resultFields;
    }

    protected function getDeal($dealId)
    {
        $resultDeal = null;
        try {


            $method = '/crm.deal.get.json';




            $url = $this->hook . $method;
            $data = [
                'id' => $dealId
            ];

            $responseData = Http::get($url, $data);
            $resultDeal = BitrixController::getBitrixRespone($responseData, 'BitrixDealDocumentService: getDeal');



            return $resultDeal;
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];
            Log::channel('telegram')->error('APRIL_ONLINE', [
                'BitrixDealDocumentService getDeal' => [
                    'message' => 'error get hook',
                    'resultDeal' => $resultDeal,
                    'responseData' => $responseData,
                    'messages' => $errorMessages

                ]
            ]);
            return $resultDeal;
        }
    }
}
