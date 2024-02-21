<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Infoblock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

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
                $comePrices = $price['cells'];




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

                //ГЕНЕРАЦИЯ ДОКУМЕНТА
                $pdf = Pdf::loadView('pdf.offer', [
                    'headerData' =>  $headerData,
                    'letterData' => $letterData,
                    'infoblocksData' => $infoblocksData
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
                $rq = $rq . ', ИНН: ' . $providerRq['inn'] . ', ';
            }
            if ($providerRq['kpp']) {
                $rq = $rq . ', КПП: ' . $providerRq['kpp'] . ', ';
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
        $totalCount = $this->getInfoblocksCount($complect);
        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];

        $codes = collect($complect)->flatMap(function ($group) {
            return collect($group['value'])->pluck('code');
        })->unique()->all();

        $infoblocks = Infoblock::whereIn('code', $codes)->get()->keyBy('code');

        $itemsPerPage = 20;
        $pages = [];
        $currentPage = [];
        $currentItemCount = 0;


        if ($styleMode === 'list') {
        } else if ($styleMode === 'table') {
            if ($descriptionMode === 0) {
                $itemsPerPage = 40;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 20;
            } else {
                $itemsPerPage = 10;
            }
        } else {
            if ($descriptionMode === 0) {
                $itemsPerPage = 60;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 30;
            } else {
                $itemsPerPage = 15;
            }
        }




        foreach ($complect as $group) {
            foreach ($group['value'] as $infoblock) {
                if (!array_key_exists('code', $infoblock) || !$infoblocks->has($infoblock['code'])) {
                    continue;
                }

                $currentInfoblock = $infoblocks->get($infoblock['code']);
                if ($currentItemCount >= $itemsPerPage) {
                    $pages[] = $currentPage;
                    $currentPage = [];
                    $currentItemCount = 0;
                }

                $currentPage[] = $currentInfoblock;
                $currentItemCount++;
            }
        }

        // Добавляем оставшиеся элементы, если они есть
        if (!empty($currentPage)) {
            $pages[] = $currentPage;
        }

        return [
            'styleMode' => $styleMode,
            'descriptionMode' => $descriptionMode,
            'pages' => $pages, // Массив "страниц", каждая содержит до 20 элементов инфоблоков
            'totalCount' => $totalCount
        ];
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
}
