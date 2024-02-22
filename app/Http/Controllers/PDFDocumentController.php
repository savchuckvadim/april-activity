<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Infoblock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use morphos\Cases;
use morphos\Russian\MoneySpeller;
use Ramsey\Uuid\Uuid;
use morphos\Russian\Cases as RussianCases;
use function morphos\Russian\pluralize;



class PDFDocumentController extends Controller
{
    public function getDocument($data)
    {
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

                $headerData  = $this->getHeaderData($providerRq, $isTwoLogo);
                $letterData  = $this->getLetterData($documentNumber, $fields, $recipient);
                $infoblocksData  = $this->getInfoblocksData($infoblocksOptions, $complect);

                $pricesData  =   $this->getPricesData($price);
                $stampsData  =   $this->getStampsData($providerRq);


                //ГЕНЕРАЦИЯ ДОКУМЕНТА
                $pdf = Pdf::loadView('pdf.offer', [
                    'headerData' =>  $headerData,
                    'letterData' => $letterData,
                    'infoblocksData' => $infoblocksData,
                    'pricesData' => $pricesData,
                    'stampsData' => $stampsData,
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

                $link = asset('storage/clients/' . $domain . '/documents/' . $data['userId'] . '/' . $resultFileName);
                // $link = $pdf->download($resultFileName);
                // return APIController::getSuccess([
                //     'price' => $price,
                //     'link' => $link,
                //     'documentNumber' => $documentNumber,
                //     'counter' => $counter,

                // ]);


                //BITRIX
                // $this->setTimeline($domain, $dealId, $link, $documentNumber);
                // $bitrixController = new BitrixController();
                // $response = $bitrixController->changeDealStage($domain, $dealId, "PREPARATION");

                return APIController::getSuccess([
                    // 'price' => $price,
                    'link' => $link,
                    // 'documentNumber' => $documentNumber,
                    // 'counter' => $counter,

                ]);
            }
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


    protected function getLetterData($documentNumber, $fields, $recipient)
    {
        $letterData = [
            'documentNumber' => null,
            'companyName' => null,
            'inn' => null,
            'positionCase' => null,
            'recipientCase' => null,
            'recipientName' => null,
            'text' => null
        ];



        if ($documentNumber) {

            $letterData['documentNumber'] = 'Исх. № ' . $documentNumber;
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





    protected function getInfoblocksData($infoblocksOptions, $complect)
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
        $withPrice = $this->getWithPrice($pages, $descriptionMode, $styleMode);
        return [
            'styleMode' => $styleMode,
            'descriptionMode' => $descriptionMode,
            'pages' => $pages,
            'withPrice' => $withPrice

        ];
    }

    protected function determineItemsPerPage($descriptionMode, $styleMode)
    {
        $itemsPerPage = 20;

        if ($styleMode === 'list') {

            if ($descriptionMode === 0) {
                $itemsPerPage = 40;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 11;
            } else {
                $itemsPerPage = 4;
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
                $itemsPerPage = 24;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 9;
            } else {
                $itemsPerPage = 6;
            }
        }

        return $itemsPerPage;
    }

    protected function getWithPrice($pages, $descriptionMode, $styleMode)
    {
        $isWithPrice = false;
        $lastPageItemsCount = 0;
        $lastPage = end($pages);
        if (is_array($lastPage) && isset($lastPage['items']) && is_array($lastPage['items'])) {
            // Получаем длину массива 'items'
            $lastPageItemsCount = count($lastPage['items']);
        }


        if ($styleMode === 'list') {

            if ($descriptionMode === 0) {
                if ($lastPageItemsCount < 20) {
                    $isWithPrice = true;
                }
            } else if ($descriptionMode === 1) {
                if ($lastPageItemsCount < 8) {
                    $isWithPrice = true;
                }
            } else {

                if ($lastPageItemsCount < 4) {
                    $isWithPrice = true;
                }
            }
        } else if ($styleMode === 'table') {
            if ($descriptionMode === 0) {
                $isWithPrice = true;
            } else if ($descriptionMode === 1) {
                if ($lastPageItemsCount < 11) {
                    $isWithPrice = true;
                }
            } else {

                if ($lastPageItemsCount < 7) {
                    $isWithPrice = true;
                }
            }
        } else {
            if ($descriptionMode === 0) {
                if ($lastPageItemsCount < 20) {
                    $isWithPrice = true;
                }
            } else if ($descriptionMode === 1) {
                if ($lastPageItemsCount < 5) {
                    $isWithPrice = true;
                }
            } else {

                if ($lastPageItemsCount < 3) {
                    $isWithPrice = true;
                }
            }
        }

        return $isWithPrice;
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





    protected function getPricesData($price)
    {
        $isTable = $price['isTable'];
        $comePrices = $price['cells'];
        $total = 'ИТОГО';
        $fullTotalstring = '';
        $totalSum = 0;        //SORT CELLS
        $sortActivePrices = $this->getSortActivePrices($comePrices);
        $allPrices =  $sortActivePrices;


        //IS WITH TOTAL 
        $withTotal = $this->getWithTotal($allPrices);
        $quantityMeasureString = '';
        $quantityString = '';
        $measureString = '';
        if ($withTotal) {
            $foundCell = null;
            foreach ($price['cells']['total'][0]['cells'] as $cell) {
                if ($cell['code'] === 'prepaymentsum') {
                    $foundCell = $cell;
                }

                if ($cell['code'] === 'quantity' && $cell['value']) {
                    if ($cell['value'] == 1) {
                        $quantityString = '1' . ' месяц';
                    } else {

                        $quantityString = pluralize($cell['value'], 'месяц', false, Cases::NOMINATIVE); // => 10 машин ;

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

            $quantityMeasureString = ' за ' .  $quantityString . ' ' . $measureString;


            if ($foundCell) {
                $totalSum = $foundCell['value'];
                $total = $total . ': ' . $totalSum;
            }

            $result = MoneySpeller::spell($foundCell['value'], MoneySpeller::RUBLE);
            $firstChar = mb_strtoupper(mb_substr($result, 0, 1, "UTF-8"), "UTF-8");
            $restOfText = mb_substr($result, 1, mb_strlen($result, "UTF-8"), "UTF-8");






            $text = ' (' . $firstChar . $restOfText . ') без НДС';
            $textTotalSum = $text;

            $fullTotalstring = $total . ' ' . $textTotalSum . $quantityMeasureString;
        }

        return [
            'isTable' => $isTable,
            'allPrices' => $allPrices,
            'withTotal' => $withTotal,
            'total' => $fullTotalstring

        ];
    }


    protected function getSortActivePrices($allPrices)
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

                                //TODO for invoice - measure
                                // $filtredCells = array_filter($product['cells'], function ($prc) {
                                //     return $prc['isActive'] == true || $prc['code'] == 'measure';
                                // });

                                $filtredCells = array_filter($product['cells'], function ($prc) {
                                    return $prc['isActive'] == true;
                                });
                                usort($filtredCells, function ($a, $b) {
                                    return $a['order'] - $b['order'];
                                });
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

    protected function getTotalPriceRow(
        $allPrices,
        $table,
        $styles,
        $contentWidth,
        $isHaveLongPrepayment,
        $numCells
    ) {
        $total = $allPrices['total'];

        if ($total) {
            if (is_array($total) && !empty($total)) {

                $product = $total[0];
                if ($product) {
                    if (is_array($product) && !empty($product) && is_array($product['cells']) && !empty($product['cells'])) {
                        $table->addRow();
                        foreach ($product['cells'] as $cell) {

                            $this->getPriceCell(false, true, $table, $styles, $cell, $contentWidth, $isHaveLongPrepayment, $numCells);
                        }
                    }
                }
            }
        }
        return $table;
    }
    protected function getWithTotal(
        $allPrices,

    ) {

        $result = false;
        $alternative =  $allPrices['alternative'];
        $general =  $allPrices['general'];
        if (is_array($alternative) && is_array($general)) {
            if (empty($alternative) && count($general) > 1) {
                $result = true;
            }
        }

        return $result;
    }


    protected function getStampsData($providerRq)
    {
        $stampsData = [
            'position' => '',
            'stamp' => '',
            'signature' => '',
            'director' => ''
        ];
        $stamps = $providerRq['stamps'];
        $signatures = $providerRq['signatures'];

        if (!empty($stamps)) {
            $stampsData['stamp'] = storage_path('app/' . $stamps[0]['path']);
        }
        if (!empty($signatures)) {
            $stampsData['signature'] = storage_path('app/' . $signatures[0]['path']);
        }



        $stampsData['position'] = $providerRq['position'] . ' ' . $providerRq['fullname'];
        if ($providerRq['type'] == 'ip') {
            $stampsData['position'] = $providerRq['fullname'];
        }



        if ($providerRq['type'] == 'org') {
            $stampsData['director']  = $providerRq['director'];
        }

        return $stampsData;
    }
}
