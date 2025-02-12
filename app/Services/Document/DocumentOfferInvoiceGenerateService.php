<?php

namespace App\Services\Document;

use App\Http\Controllers\ALogController;


class DocumentOfferInvoiceGenerateService
{
    protected $processor;
    protected $infoblocks;
    public function __construct($domain,   $data)
    {
        $filePath = 'app/public/konstructor/templates/offer/gsr.bitrix24.ru';

        $fullPath = storage_path($filePath . '/offer.docx');

        $this->processor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);
        // $this->infoblocks = $data['infoblock'];
        $this->infoblocks = [
            'Нормативно-правовые акты' => [
                ['infoblock_title' => 'Законодательство России', 'infoblock_description' => 'Блок, который содержит ...'],
                ['infoblock_title' => 'Отраслевое законодательство', 'infoblock_description' => 'Блок содержит ...'],
            ],
            'Энциклопедии решений' => [
                ['infoblock_title' => 'Энциклопедия 1', 'infoblock_description' => 'Описание Э1'],
                ['infoblock_title' => 'Энциклопедия 2', 'infoblock_description' => 'Описание Э2'],
            ]
        ];
    }

    public function getGenerateDocumentFromTemplate(

        // $data = [
        //     'infoblock' => $infoblockService->getInfoblocksData(),
        //     'offer' => $offerService->getOfferData(),
        //     'invoice' => [
        //         'price' => $invoiceService->getInvoicePricesData($price,  true, $alternativeSetId)
        //     ],
        //     // 'contract' => $contract,
        //     // 'supply' => $documentType,
        // ];

    )
    {





        // Клонируем блоки групп

        // Сохраняем итоговый документ
        $hash = md5(uniqid(mt_rand(), true));
        $outputFileName = 'offer_result.docx';
        $outputFilePath = storage_path('app/public/konstructor/result_test/');

        $this->letterWithPrice();
        $this->processInfoblocks();
        // Сохраняем файл Word в формате .docx


        if (!file_exists($outputFilePath)) {
            mkdir($outputFilePath, 0775, true); // Создать каталог с правами доступа
        }

        // // Проверить доступность каталога для записи
        if (!is_writable($outputFilePath)) {
            throw new \Exception("Невозможно записать в каталог: $outputFilePath");
        }



        $fullOutputFilePath = $outputFilePath . $outputFileName;
        $this->processor->saveAs($fullOutputFilePath);

        return ['file' => $fullOutputFilePath];
    }

    protected function letterWithPrice()
    {
        // Пример условия
        $withPrice = true;
        $processor = $this->processor;
        // Условная вставка блоков
        if ($withPrice) {
            // Вставляем блок с ценой
            $processor->cloneBlock('if_letterWithPrice', 1, true, false, );
            $processor->deleteBlock('if_letterWithoutPrice');
        } else {
            // Вставляем блок без цены
            $processor->cloneBlock('if_letterWithoutPrice', 0, true, false);
            $processor->deleteBlock('if_letterWithPrice');
        }
        $processor->setValue('sale_text', 'Скидка 20% на весь ассортимент!');

    }
    protected function processInfoblocks()
    {

        $replacements = [];
        $groupIndex = 0;

        foreach ($this->infoblocks as $groupName => $infoblocks) {
            $replacements[] = [
                'infoblock_group' => $groupName,
                'infoblock_title_0' => '${infoblock_title_' . $groupIndex . '}',
                'infoblock_description_0' => '${infoblock_description_' . $groupIndex . '}'
            ];
            $groupIndex++;
        }

        $this->processor->cloneBlock('block_group', 0, true, false, $replacements);

        // Теперь клонируем строки для каждой группы
        $groupIndex = 0;
        foreach ($this->infoblocks as $groupName => $infoblocks) {
            $rows = [];

            foreach ($infoblocks as $infoblock) {
                $rows[] = [
                    'infoblock_title_' . $groupIndex => $infoblock['infoblock_title'],
                    'infoblock_description_' . $groupIndex => $infoblock['infoblock_description']
                ];
            }

            // Клонируем строки таблицы внутри каждой группы
            $this->processor->cloneRowAndSetValues('infoblock_title_' . $groupIndex, $rows);

            $groupIndex++;
        }
    }
}
