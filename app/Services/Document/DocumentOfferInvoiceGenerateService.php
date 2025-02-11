<?php

namespace App\Services\Document;


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
                'items' => [
                    [
                        'title' => '',
                        'description' => ''
                    ],
                    [
                        'title' => '',
                        'description' => ''
                    ],
                ]
            ],
            [
                'groupName' => 'name2',
                'items' => [
                    [
                        'title' => '',
                        'description' => ''
                    ],
                    [
                        'title' => '',
                        'description' => ''
                    ],
                ]
            ]
        ];
        // $templateProcessor->cloneRowAndSetValues('complect_name', $complects);
        // $templateProcessor->setValue('client_company_name', $clientCompanyFullName);
        // Клонируем группы
        $templateProcessor->cloneRow('infoblock_group', count($groups));

        foreach ($groups as $groupIndex => $group) {
            $groupNumber = $groupIndex + 1;

            // Устанавливаем имя группы
            $templateProcessor->setValue("infoblock_group#{$groupNumber}", $group['groupName']);

            // Клонируем элементы внутри группы
            $templateProcessor->cloneRow("infoblock_title#{$groupNumber}", count($group['items']));

            foreach ($group['items'] as $itemIndex => $item) {
                $itemNumber = $itemIndex + 1;

                // Устанавливаем значения для каждого элемента в группе
                $templateProcessor->setValue("infoblock_title#{$groupNumber}_{$itemNumber}", $item['title']);
                $templateProcessor->setValue("infoblock_description#{$groupNumber}_{$itemNumber}", $item['description']);
            }
        }

        // Сохраняем итоговый документ
        $hash = md5(uniqid(mt_rand(), true));
        $outputFileName = 'offer_result.docx';
        $outputFilePath = storage_path('app/public/konstructor/result_test/' . $hash);



        // Сохраняем файл Word в формате .docx
     

        if (!file_exists($outputFilePath)) {
            mkdir($outputFilePath, 0775, true); // Создать каталог с правами доступа
        }

        // // Проверить доступность каталога для записи
        if (!is_writable($outputFilePath)) {
            throw new \Exception("Невозможно записать в каталог: $outputFilePath");
        }



        $fullOutputFilePath = $outputFilePath . '/' . $outputFileName;
        $templateProcessor->saveAs($fullOutputFilePath);
   
        return ['file' => $fullOutputFilePath];
    }
}
