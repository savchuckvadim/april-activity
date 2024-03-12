<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Infoblock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use morphos\Cases;
use morphos\Russian\MoneySpeller;
use Ramsey\Uuid\Uuid;
use morphos\Russian\Cases as RussianCases;
use morphos\Russian\TimeSpeller;

use function morphos\Russian\pluralize;



class PDFDocumentController extends Controller
{
    public function getDocument($data)
    {
        $pdfData = [];
        try {
            if ($data &&  isset($data['template'])) {
                $template = $data['template'];
                if ($template && isset($template['id'])) {


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
                    //infoblocks data
                    $infoblocksOptions = [
                        'description' => $data['infoblocks']['description']['current'],
                        'style' => $data['infoblocks']['style']['current']['code'],
                    ];
                    $complect = $data['complect'];
                    $complectName = '';

                    foreach ($data['price']['cells']['total'][0]['cells'] as $cell) {

                        if ($cell['code'] === 'name') {
                            $complectName = $cell['value'];
                        }
                    }



                    //price
                    $price = $data['price'];

                    //SORT CELLS
                    // $sortActivePrices = $this->getSortActivePrices($comePrices);
                    // $allPrices =  $sortActivePrices;
                    // $general = $allPrices['general'];
                    // $alternative = $allPrices['alternative'];


                    //manager
                    $manager = $data['manager'];
                    //UF_DEPARTMENT
                    //SECOND_NAME


                    //fields
                    $fields = $data['template']['fields'];
                    $recipient = $data['recipient'];


                    //document number
                    $documentNumber = CounterController::getCount($templateId);



                    //invoice
                    $invoices = [];
                    $invoiceBaseNumber =  preg_replace('/\D/', '', $documentNumber);
                    $invoiceData = $data['invoice'];

                    $isGeneralInvoice = false;
                    $isAlternativeInvoices = false;

                    if (isset($invoiceData['one']) && isset($invoiceData['many'])) {
                        $isGeneralInvoice = $invoiceData['one']['isActive'] && $invoiceData['one']['value'];
                        $isAlternativeInvoices = $invoiceData['many']['isActive'] && $invoiceData['many']['value'];
                    }



                    //general
                    $comePrices = $price['cells'];
                    $productsCount = $this->getProductsCount($comePrices);



                    //document data
                    $headerData  = $this->getHeaderData($providerRq, $isTwoLogo);
                    $doubleHeaderData  = $this->getDoubleHeaderData($providerRq);
                    $footerData  = $this->getFooterData($manager);
                    $letterData  = $this->getLetterData($documentNumber, $fields, $recipient, $domain);
                    $infoblocksData  = $this->getInfoblocksData($infoblocksOptions, $complect, $complectName, $productsCount);

                    $pricesData  =   $this->getPricesData($price, $infoblocksData['withPrice'], false);
                    $stampsData  =   $this->getStampsData($providerRq, false);
                    // $invoiceData  =   $this->getInvoiceData($invoiceBaseNumber, $providerRq, $recipient, $price);



                    $pdfData = [
                        'headerData' =>  $headerData,
                        'doubleHeaderData' =>  $doubleHeaderData,
                        'footerData' =>  $footerData,
                        'letterData' => $letterData,
                        'infoblocksData' => $infoblocksData,
                        'pricesData' => $pricesData,
                        'stampsData' => $stampsData,
                    ];


                    //ГЕНЕРАЦИЯ ДОКУМЕНТА
                    $pdf = Pdf::loadView('pdf.offer', [
                        'headerData' =>  $headerData,
                        'doubleHeaderData' =>  $doubleHeaderData,
                        'footerData' =>  $footerData,
                        'letterData' => $letterData,
                        'infoblocksData' => $infoblocksData,
                        'pricesData' => $pricesData,
                        'stampsData' => $stampsData,
                        // 'invoiceData' => $invoiceData,
                    ]);







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
                    $links = [];
                    $offerLink = asset('storage/clients/' . $domain . '/documents/' . $data['userId'] . '/' . $resultFileName);

                    // $link = $pdf->download($resultFileName);
                    // return APIController::getSuccess([
                    //     'price' => $price,
                    //     'link' => $link,
                    //     'documentNumber' => $documentNumber,
                    //     'counter' => $counter,

                    // ]);
                    array_push($links, $offerLink);
                    if ($isGeneralInvoice) {
                        $generalInvoice = $this->getInvoice($data, $isTwoLogo,  $documentNumber);
                        array_push($invoices, $generalInvoice);
                        array_push($links, $generalInvoice);
                    }
                    if ($isAlternativeInvoices) {
                        foreach ($data['price']['cells']['alternative'] as $key => $product) {
                            Log::error('product',  ['product' => $product]);
                            $alternativeInvoice = $this->getInvoice($data, $isTwoLogo,  $documentNumber, false, $key);
                            Log::error('alternativeInvoice', ['invoice' => $alternativeInvoice]);
                            array_push($invoices, $alternativeInvoice);
                            array_push($links, $alternativeInvoice);
                        }
                    }

                    //BITRIX

                    $bitrixController = new BitrixController();
                    $response = $bitrixController->changeDealStage($domain, $dealId, "PREPARATION");
                    $this->setTimeline($domain, $dealId, $links, $documentNumber);


                    return APIController::getSuccess([
                        'infoblocksData' => $infoblocksData,
                        // 'link' => $links[1],
                        'link' => $offerLink,
                        'links' => $links,
                        'pdf' => $pdfData
                        // 'documentNumber' => $documentNumber,
                        // 'counter' => $counter,

                    ]);
                }
            }
        } catch (\Throwable $th) {

            return APIController::getError($th->getMessage(), ['come' => $data, 'pdf' => $pdfData]);
        }
    }
    public function getInvoice($data, $isTwoLogo,  $documentNumber, $isGeneral = true, $alternativeSetId = 0)
    {
        if ($data &&  isset($data['template'])) {
            $template = $data['template'];
            if ($template && isset($template['id'])) {


                $templateId = $template['id'];
                $domain = $data['template']['portal'];
                $dealId = $data['dealId'];
                $providerRq = $data['provider']['rq'];





                //price
                $price = $data['price'];

                //SORT CELLS
                // $sortActivePrices = $this->getSortActivePrices($comePrices);
                // $allPrices =  $sortActivePrices;
                // $general = $allPrices['general'];
                // $alternative = $allPrices['alternative'];


                //manager
                // $manager = $data['manager'];
                //UF_DEPARTMENT
                //SECOND_NAME


                //recipient

                $recipient = $data['recipient'];


                //invoice
                $invoiceBaseNumber =  preg_replace('/\D/', '', $documentNumber);
                if (!$isGeneral) {
                    $invoiceBaseNumber = $invoiceBaseNumber . '-' . $alternativeSetId + 1;
                }


                //general
                $comePrices = $price['cells'];
                $productsCount = $this->getProductsCount($comePrices);



                //document data
                $headerData  = $this->getHeaderData($providerRq, $isTwoLogo);
                $doubleHeaderData  = $this->getDoubleHeaderData($providerRq);
                $stampsData  =   $this->getStampsData($providerRq, true);
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
    }


    public function setTimeline($domain, $dealId, $links, $commentText)
    {
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
            $response = Http::get($url, $data);
            if ($response) {
                if (isset($response['result'])) {
                    return $response['result'];
                } else {
                    if (isset($response['error_description'])) {
                        return $response['result'];
                    }
                }
            }
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), ['data' => [$domain, $dealId, $commentLink, $commentText]]);
        }
    }



    protected function getHeaderData($providerRq, $isTwoLogo)
    {

        $headerData = [
            'isTwoLogo' => $isTwoLogo,
            'rq' => '',
            'logo_1' => null,
            'logo_2' => null,
        ];
        $rq = '';
        if (!$isTwoLogo) {
            $rq = $providerRq['fullname'];
            if ($providerRq['inn']) {
                $rq = $rq . ', ИНН: ' . $providerRq['inn'];
            }
            if ($providerRq['kpp']) {
                $rq = $rq . ', КПП: ' . $providerRq['kpp'];
            }

            $rq = $rq . ', ' . $providerRq['primaryAdresss'];
            if ($providerRq['phone']) {
                $rq = $rq . ', ' . $providerRq['phone'];
            }
            if ($providerRq['email']) {
                $rq = $rq . ', ' . $providerRq['email'];
            }
        } else {


            if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos']) && count($providerRq['logos']) > 1) {
                $fullPath2 = storage_path('app/' .  $providerRq['logos'][1]['path']);
                $headerData['logo_2'] = $fullPath2;
            }
        }


        if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos'])) {
            $fullPath1 = storage_path('app/' .  $providerRq['logos'][0]['path']);
            $headerData['logo_1'] =  $fullPath1;
        }
        $headerData['rq'] = $rq;
        return $headerData;
    }

    protected function getDoubleHeaderData($providerRq)
    {
        $headerData = [
            'first' => '',
            'second' => '',

        ];
        $rq = '';
        $phone = null;
        $email = null;
        $first = $providerRq['fullname'];
        if ($providerRq['inn']) {
            $first = $first . ', ИНН: ' . $providerRq['inn'];
        }
        if ($providerRq['kpp']) {
            $first = $first . ', КПП: ' . $providerRq['kpp'];
        }
        if ($providerRq['primaryAdresss']) {
            $first = $first . ', ' . $providerRq['primaryAdresss'];
        }
        if ($providerRq['rs']) {
            $first = $first . ', р/c: ' . $providerRq['rs'];
        }

        $second = '';




        if ($providerRq['phone']) {
            $phone = $providerRq['phone'];
            $second = $phone;
        }

        if ($providerRq['email']) {
            $email = 'e-mail: ' . $providerRq['email'];
        }
        $second = $second . '' . $email;
        $headerData = [
            'first' => $first,
            'second' => $second,
            'phone' =>  $phone,
            'email' =>  $email,

        ];

        return $headerData;
    }
    protected function getFooterData($manager)
    {
        $footerData = [
            'managerPosition' => '',
            'name' => '',
            'email' => '',
            'phone' => '',

        ];
        if ($manager) {
            $managerPosition = 'Ваш персональный менеджер';

            if (isset($manager['WORK_POSITION'])) {
                if ($manager['WORK_POSITION']) {
                    $managerPosition = $manager['WORK_POSITION'];
                }
            }
            $managerName = $manager['NAME'];
            $managerLastName = $manager['LAST_NAME'];
            $name =  $managerName . ' ' . $managerLastName;

            $managerEmail = $manager['EMAIL'];
            $email = null;
            if ($managerEmail) {
                $email = 'e-mail: ' . $managerEmail;
            }

            $workPhone = $manager['WORK_PHONE'];
            $mobilePhone = $manager['PERSONAL_MOBILE'];
            $phone = $workPhone;
            if (!$phone) {
                $phone = $mobilePhone;
            }
            if ($phone) {
                $phone = 'телелефон: ' . $phone;
            }
            $footerData = [
                'managerPosition' =>  $managerPosition,
                'name' =>  $name,
                'email' =>  $email,
                'phone' =>  $phone,

            ];
        }

        return $footerData;
    }
    protected function getLetterData($documentNumber, $fields, $recipient, $domain)
    {
        $isLargeLetterText = false;

        if ($domain == 'april-garant.bitrix24.ru') {
            $isLargeLetterText = true;
        }


        $date = $this->getToday();
        $letterData = [
            'documentNumber' => null,
            'documentDate' => null,
            'companyName' => null,
            'inn' => null,
            'positionCase' => null,
            'recipientCase' => null,
            'recipientName' => null,
            'text' => null,
            'isLargeLetterText' => $isLargeLetterText

        ];




        if ($documentNumber) {
            $letterData['documentNumber'] = 'Исх. № ' . $documentNumber;
            $letterData['documentDate'] = ' от ' . $date;
        }



        if ($recipient) {
            if (isset($recipient['companyName'])) {
                if ($recipient['companyName']) {
                    $letterData['companyName'] = $recipient['companyName'];
                }
            }
            if (isset($recipient['inn'])) {
                if ($recipient['inn']) {
                    $letterData['inn'] = 'ИНН: ' . $recipient['inn'];
                }
            }
            if (isset($recipient['positionCase'])) {
                if ($recipient['positionCase']) {
                    $letterData['positionCase']  = $recipient['positionCase'];
                }
            }
            if (isset($recipient['recipientCase'])) {
                if ($recipient['recipientCase']) {
                    $letterData['recipientCase'] = $recipient['recipientCase'];
                }
            }
        }

        // $section->addTextBreak(1);
        if (isset($recipient['recipient'])) {
            if ($recipient['recipient']) {

                $letterData['recipientName'] = $recipient['recipient'];
            }
        }


        $letterText = '';
        foreach ($fields as $field) {
            if ($field && $field['code']) {
                if (
                    $field['code'] == 'letter' || $field['bitrixTemplateId'] == 'letter'

                ) {
                    if ($field['description']) {
                        $letterText = $field['description'];
                    }
                }
            }
        }
        $letterData['text'] = $letterText;
        // $parts = preg_split('/<color>|<\/color>/', $letterText);

        // $textRun = $section->addTextRun();

        // $inHighlight = false;
        // foreach ($parts as $part) {
        //     // Разбиваем часть на подстроки по символам переноса строки
        //     $subparts = preg_split("/\r\n|\n|\r/", $part);
        //     // foreach ($subparts as $subpart) {
        //     //     if ($inHighlight) {
        //     //         // Добавление выделенного текста
        //     //         // $textRun->addText($subpart, $corporateletterTextStyle, $styles['paragraphs']['align']['both']);
        //     //     } else {
        //     //         // Добавление обычного текста
        //     //         // $textRun->addText($subpart, $letterTextStyle, $styles['paragraphs']['align']['both']);
        //     //     }
        //     //     // Добавление разрыва строки после каждой подстроки, кроме последней
        //     //     // if ($subpart !== end($subparts)) {
        //     //     //     $textRun->addTextBreak(1);
        //     //     // }
        //     // }
        //     $inHighlight = !$inHighlight;
        // }



        return $letterData;
    }





    protected function getInfoblocksData($infoblocksOptions, $complect, $complectName, $productsCount)
    {
        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];
        $itemsPerPage = $this->determineItemsPerPage($descriptionMode, $styleMode);

        $withPrice = false;
        $pages = [];
        $currentPage = [
            'groups' => [],
            'items' => []

        ];
        $currentPageItemsCount = 0;

        foreach ($complect as $group) {
            $groupItems = [];
            foreach ($group['value'] as $infoblock) {
                if (!array_key_exists('code', $infoblock)) {
                    continue;
                }

                $infoblockData = Infoblock::where('code', $infoblock['code'])->first();
                if ($infoblockData) {
                    $groupItems[] = $infoblockData;
                    array_push($currentPage['items'], $infoblockData);
                }
            }

            // Распределение элементов группы по страницам
            while (!empty($groupItems)) {
                $spaceLeft = $itemsPerPage - $currentPageItemsCount; // Сколько элементов помещается на страницу
                if ($spaceLeft == 0) {
                    // Если на текущей странице нет места, переходим к следующей
                    $pages[] = $currentPage;
                    $currentPage = [
                        'groups' => [],
                        'items' => []

                    ];
                    $currentPageItemsCount = 0;
                    $spaceLeft = $itemsPerPage;
                }

                $itemsToAdd = array_splice($groupItems, 0, $spaceLeft); // Элементы, которые поместятся на страницу
                if (!empty($itemsToAdd)) {
                    // Добавляем часть группы на текущую страницу
                    $currentPage['groups'][] = [
                        'name' => $group['groupsName'],
                        'items' => $itemsToAdd
                    ];
                    $currentPageItemsCount += count($itemsToAdd);
                }
            }
        }

        // Добавляем последнюю страницу, если она содержит элементы
        if (!empty($currentPage['groups'])) {
            $pages[] = $currentPage;
        }
        $withPrice = $this->getWithPrice($pages, $descriptionMode, $styleMode, $productsCount);
        $result = [
            'styleMode' => $styleMode,
            'descriptionMode' => $descriptionMode,
            'pages' => $pages,
            'withPrice' => $withPrice,
            'complectName' => $complectName,


        ];

        return $result;
    }



    protected function getInfoblocksCount($complect)
    {
        $result = [
            'groups' => 0,
            'infoblocks' => 0
        ];


        foreach ($complect as $group) {
            $result['groups'] += 1;
            if (isset($group['value'])) {
                foreach ($group['value'] as $infoblock) {
                    $result['infoblocks'] += 1;
                }
            }
        }
        return  $result;
    }
    protected function determineItemsPerPage($descriptionMode, $styleMode)
    {
        $itemsPerPage = 20;

        if ($styleMode === 'list') {

            if ($descriptionMode === 0) {
                $itemsPerPage = 40;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 9;
            } else {
                $itemsPerPage = 6;
            }
        } else if ($styleMode === 'table') {
            if ($descriptionMode === 0) {
                $itemsPerPage = 60;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 16;
            } else {
                $itemsPerPage = 8;
            }
        } else {
            if ($descriptionMode === 0) {
                $itemsPerPage = 27;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 10;
            } else {
                $itemsPerPage = 7;
            }
        }

        return $itemsPerPage;
    }




    protected function getPricesData($price, $withPrice = false,  $isInvoice = null)
    {
        $isTable = $price['isTable'];
        $comePrices = $price['cells'];
        $total = '';
        $fullTotalstring = '';
        $totalSum = 0;        //SORT CELLS
        $sortActivePrices = $this->getSortActivePrices($comePrices, $isInvoice);
        $allPrices =  $sortActivePrices;


        //IS WITH TOTAL 
        $withTotal = $this->getWithTotal($comePrices);

        $quantityMeasureString = '';
        $quantityString = '';
        $measureString = '';
        $contract = null;
        foreach ($price['cells']['total'][0]['cells'] as $contractCell) {
            if ($contractCell['code'] === 'contract') {
                $contract = $contractCell['value'];
            }
        }
        // if ($withTotal) {
        $foundCell = null;
        foreach ($price['cells']['total'][0]['cells'] as $cell) {
            if ($cell['code'] === 'prepaymentsum') {
                $foundCell = $cell;
            }

            if ($cell['code'] === 'quantity' && $cell['value']) {
                if ($contract['shortName'] !== 'internet' && $contract['shortName'] !== 'proxima') {
                    $quantityString =  TimeSpeller::spellUnit($contract['prepayment'], TimeSpeller::MONTH);
                } else {
                    $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT);

                    // Преобразуем результат в число
                    $quantity = intval($numberString);
                    $quantityString = TimeSpeller::spellUnit($quantity, TimeSpeller::MONTH);
                }
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
        // }

        return [
            'isTable' => $isTable,
            'isInvoice' => $isInvoice,
            'allPrices' => $allPrices,
            'withPrice' => $withPrice,
            'withTotal' => $withTotal,
            'total' => $fullTotalstring

        ];
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
            foreach ($price['cells']['alternative'][0]['cells'] as $contractCell) {
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

            if ($cell['code'] === 'quantity' && $cell['value']) {
                if ($contract['shortName'] !== 'internet' && $contract['shortName'] !== 'proxima') {

                    $qcount = $contract['prepayment'] * $cell['value'];

                    $quantityString =  TimeSpeller::spellUnit($qcount, TimeSpeller::MONTH);
                } else {
                    $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT);

                    // Преобразуем результат в число
                    $quantity = intval($numberString);
                    $quantityString = TimeSpeller::spellUnit($quantity, TimeSpeller::MONTH);
                }
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

    protected function getWithPrice($pages, $descriptionMode, $styleMode, $productsCount)
    {
        $isWithPrice = false;

        $lastPageItemsCount = 0;
        $lastPage = end($pages);
        if (is_array($lastPage) && isset($lastPage['groups']) && is_array($lastPage['groups'])) {
            $lastPageGroups = $lastPage['groups'];
            foreach ($lastPageGroups as  $lastPageGroup) {
                if (isset($lastPageGroup['items']) && is_array($lastPageGroup['items'])) {
                    $currentGrupItems = $lastPageGroup['items'];
                    $currentGrupItemsCount = count($currentGrupItems);
                    $lastPageItemsCount = $lastPageItemsCount + $currentGrupItemsCount;
                }
            }
        }

        if ($productsCount < 4) {

            if ($styleMode === 'list') {

                if ($descriptionMode === 0) {
                    if ($lastPageItemsCount < 20) {
                        $isWithPrice = true;
                    }
                } else if ($descriptionMode === 1) {
                    if ($lastPageItemsCount < 5) {
                        $isWithPrice = true;
                    }
                } else {

                    if ($lastPageItemsCount < 5) {
                        $isWithPrice = true;
                    }
                }
            } else if ($styleMode === 'table') {

                if ($descriptionMode === 0) {
                    if ($lastPageItemsCount < 38) {
                        $isWithPrice = true;
                    }
                } else if ($descriptionMode === 1) {
                    if ($lastPageItemsCount < 9) {
                        $isWithPrice = true;
                    }
                } else {

                    if ($lastPageItemsCount < 7) {
                        $isWithPrice = true;
                    }
                }
            } else {
                if ($descriptionMode === 0) {
                    if ($lastPageItemsCount < 15) {
                        $isWithPrice = true;
                    }
                } else if ($descriptionMode === 1) {
                    if ($lastPageItemsCount < 6) {
                        $isWithPrice = true;
                    }
                } else {

                    if ($lastPageItemsCount < 4) {
                        $isWithPrice = true;
                    }
                }
            }
        }


        return $isWithPrice;
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
    protected function getIsHaveLongPrepayment($allPrices)
    {
        $isHaveLongPrepayment = false;
        foreach ($allPrices['general'][0]['cells'] as $prccll) {
            if (($prccll['code'] == 'contractquantity' && $prccll['isActive']) ||
                ($prccll['code'] == 'prepayment' && $prccll['isActive']) ||
                ($prccll['code'] == 'contractsum' && $prccll['isActive']) ||
                ($prccll['code'] == 'prepaymentsum' && $prccll['isActive'])
            ) {

                $isHaveLongPrepayment = true; // Установить в true, если условие выполнено
                break; // Прекратить выполнение цикла, так как условие уже выполнено
            }
        }

        return $isHaveLongPrepayment;
    }


    protected function getWithTotal(
        $allPrices,

    ) {

        $result = false;
        $alternative =  $allPrices['alternative'];
        $general =  $allPrices['general'];



        if (is_array($alternative) && is_array($general)) {

            if (empty($alternative)) { // если нет товаров для сравнения


                if (count($general) > 1) {  //если больше одного основного товара
                    $result = true;
                }

                foreach ($general[0]['cells'] as $key => $cell) {
                    if ($cell['code'] === 'quantity') {            //если есть количество
                        if ($cell['value'] > 1) {
                            $result = true;
                        }
                    }

                    if ($cell['code'] === 'contract') {              //если какой-нибудь навороченный контракт
                        if (isset($cell['value']) && isset($cell['value']['shortName'])) {
                            if ($cell['value']['shortName'] !== 'internet' && $cell['value']['shortName'] !== 'proxima') {
                                $result = true;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    protected function getProductsCount(
        $allPrices,

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

    protected function getStampsData($providerRq, $isInvoice)
    {
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
        $invoiceData = [
            // 'stampsData' => $stampsData,
            'rq' => $providerRq,

            'main' => [
                'rq' => $providerRq,
                'recipient' => $recipient,
                'number' => $invoiceNumber,
            ],


            'pricesData' => $pricesData,

        ];

        return $invoiceData;
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
}
