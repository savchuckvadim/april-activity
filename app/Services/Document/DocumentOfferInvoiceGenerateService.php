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
        $groups =
            [
                'Нормативно-правовые акты' => [
                    ['infoblock_title' => 'Законодательство России', 'infoblock_description' => 'Блок, который содержит ...'],
                    ['infoblock_title' => 'Отраслевое законодательство', 'infoblock_description' => 'Блок содержит ...'],
                ],
                'Энциклопедии решений' => [
                    ['infoblock_title' => 'Энциклопедия 1', 'infoblock_description' => 'Описание Э1'],
                    ['infoblock_title' => 'Энциклопедия 2', 'infoblock_description' => 'Описание Э2'],
                ]
            ];
        // Клонируем блоки групп
        $replacements = [];
        $groupIndex = 0;

        foreach ($groups as $groupName => $infoblocks) {
            $replacements[] = [
                'group' => $groupName,
                'infoblock_title_0' => '${infoblock_title_' . $groupIndex . '}',
                'infoblock_description_0' => '${infoblock_description_' . $groupIndex . '}'
            ];
            $groupIndex++;
        }

        $templateProcessor->cloneBlock('block_group', 0, true, false, $replacements);

        // Теперь клонируем строки для каждой группы
        $groupIndex = 0;
        foreach ($groups as $groupName => $infoblocks) {
            $rows = [];

            foreach ($infoblocks as $infoblock) {
                $rows[] = [
                    'infoblock_title_' . $groupIndex => $infoblock['infoblock_title'],
                    'infoblock_description_' . $groupIndex => $infoblock['infoblock_description']
                ];
            }

            // Клонируем строки таблицы внутри каждой группы
            $templateProcessor->cloneRowAndSetValues('infoblock_title_' . $groupIndex, $rows);

            $groupIndex++;
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
