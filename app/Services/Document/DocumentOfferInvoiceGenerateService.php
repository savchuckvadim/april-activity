<?php

namespace App\Services\Document;
use App\Http\Controllers\ALogController;


class DocumentOfferInvoiceGenerateService
{

    public static function getGenerateDocumentFromTemplate(
        $data
        // $data = [
        //     'infoblock' => $infoblockService->getInfoblocksData(),
        //     'offer' => $offerService->getOfferData(),
        //     'invoice' => [
        //         'price' => $invoiceService->getInvoicePricesData($price,  true, $alternativeSetId)
        //     ],
        //     // 'contract' => $contract,
        //     // 'supply' => $documentType,
        // ];

    ) {

        $filePath = 'app/public/konstructor/templates/offer/gsr.bitrix24.ru';

        $fullPath = storage_path($filePath . '/offer.docx');

        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);

        $iblockData = $data['infoblock'];
        // $result = [
        //     'styleMode' => $styleMode,
        //     'descriptionMode' => $descriptionMode,
        //     'pages' => $pages,
        //     'withPrice' => $withPrice,
        //     'complectName' => $complectName,
        //       'infoblocks' => $this->getAllInfoblocks()


        // ]
        //         ${infoblock_group}
        // ${infoblock_title} - ${infoblock_description}
        $groups = [
            [
                'groupName' => 'name1',
                'infoblocks' => [
                    [
                        'infoblock_title' => 'sdf',
                        'infoblock_description' => 'sdh5w54444'
                    ],
                    [
                        'infoblock_title' => '43trt',
                        'infoblock_description' => 'gsgw45y345y'
                    ],
                ]
            ],
            [
                'groupName' => 'name2',
                'infoblocks' => [
                    [
                        'infoblock_title' => '345345',
                        'infoblock_description' => 'igdi7tgdkjd'
                    ],
                    [
                        'infoblock_title' => '34535',
                        'infoblock_description' => '7igdasdbasd'
                    ],
                ]
            ]
        ];
        // $groups = $data['infoblock']['infoblocks'];
        // $templateProcessor->cloneRowAndSetValues('complect_name', $complects);
        // $templateProcessor->setValue('client_company_name', $clientCompanyFullName);
        // $templateProcessor->cloneRow('infoblock_group', count($groups));

        foreach ($groups as $groupIndex => $group) {
            $groupNumber = $groupIndex + 1;
            foreach ($group['infoblocks'] as $iblock) {
                if (is_object($iblock)) {
                    $iblock = (array) $iblock;
                }
                ALogController::push('iblock', $iblock);

            }
            // Устанавливаем имя группы
            // $templateProcessor->setValue("group_name#{$groupNumber}", $group['groupsName']);

            // Клонируем инфоблоки внутри группы
            $templateProcessor->cloneRow("infoblock_title", count($group['infoblocks']));
        
            foreach ($group['infoblocks'] as $infoblockIndex => $infoblock) {
                if (is_object($infoblock)) {
                    $infoblock = (array) $infoblock;
                }
                $infoblockNumber = $infoblockIndex + 1;
        
                $templateProcessor->setValue("infoblock_title#{$groupNumber}_{$infoblockNumber}", $infoblock['infoblock_title']);
                $templateProcessor->setValue("infoblock_description#{$groupNumber}_{$infoblockNumber}", $infoblock['infoblock_description']);
            }
        }
        
        // Сохраняем итоговый документ
        $hash = md5(uniqid(mt_rand(), true));
        $outputFileName = 'offer_result.docx';
        $outputFilePath = storage_path('app/public/konstructor/result_test/');



        // Сохраняем файл Word в формате .docx
     

        if (!file_exists($outputFilePath)) {
            mkdir($outputFilePath, 0775, true); // Создать каталог с правами доступа
        }

        // // Проверить доступность каталога для записи
        if (!is_writable($outputFilePath)) {
            throw new \Exception("Невозможно записать в каталог: $outputFilePath");
        }



        $fullOutputFilePath = $outputFilePath . $outputFileName;
        $templateProcessor->saveAs($fullOutputFilePath);
   
        return ['file' => $fullOutputFilePath];
    }
}
