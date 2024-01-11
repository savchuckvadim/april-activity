<?php

namespace App\Http\Controllers;

use App\Models\Infoblock;
use Ramsey\Uuid\Uuid;
use PhpOffice\PhpWord\Shared\Converter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery\Undefined;

class DocumentController extends Controller
{
    public function getDocument($data)

    {
        try {
            $infoblocksOptions = [
                'description' => $data['infoblocks']['description']['current'],
                'style' => $data['infoblocks']['style']['current']['code'],
            ];

            $complect = $data['complect'];

            $templateType = $data['template']['type'];


            //result document
            $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/' . $data['userId']);


            if (!file_exists($resultPath)) {
                mkdir($resultPath, 0775, true); // Создать каталог с правами доступа
            }

            // Проверить доступность каталога для записи
            if (!is_writable($resultPath)) {
                throw new \Exception("Невозможно записать в каталог: $resultPath");
            }



            $uid = Uuid::uuid4()->toString();
            $resultFileName = $templateType . '_' . $uid . '.docx';



            $phpWord = new \PhpOffice\PhpWord\PhpWord();
            $phpWord->addParagraphStyle('Heading2', ['alignment' => 'center']);


            //стиль страницы 
            $sectionStyle = array(
                'pageSizeW' => Converter::inchToTwip(8.5), // ширина страницы
                'pageSizeH' => Converter::inchToTwip(11),   // высота страницы
                'marginLeft' => Converter::inchToTwip(0.5),
                'marginRight' => Converter::inchToTwip(0.5),
                'lang' => 'ru-RU',
                'heading' => ['bold' => true, 'size' => 16, 'name' => 'Arial'],
                'text' => ['size' => 12, 'name' => 'Arial'],
                'textSmall' => ['size' => 10, 'name' => 'Arial'],
                'textSmallBold' => ['size' => 10, 'name' => 'Arial', 'bold' => true],
                'textBold' => ['size' => 12, 'name' => 'Arial', 'bold' => true],

            );

            // Создаем стиль абзаца
            $paragraphStyle = array(
                'spaceAfter' => 0,    // Интервал после абзаца
                'spaceBefore' => 0,   // Интервал перед абзацем
                'lineHeight' => 1.15,  // Высота строки
                // Другие параметры стиля абзаца...
            );
            // $languageEnGbStyle = array('lang' => 'ru-RU');

            $section = $phpWord->addSection($sectionStyle);
            $this->getPriceSection($section, $data['price']);
            $this->getInfoblocks($infoblocksOptions, $complect, $section, $paragraphStyle);

            // //СОХРАНЕНИЕ ДОКУМЕТА
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($resultPath . '/' . $resultFileName);

            // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ
            $link = asset('storage/clients/' . $data['domain'] . '/documents/' . $data['userId'] . '/' . $resultFileName);

            return APIController::getSuccess([
                'data' => $data,
                'link' => $link,
                // 'testInfoblocks' => $testInfoblocks
            ]);
        } catch (\Throwable $th) {
            return APIController::getError(
                'something wrong ' . $th->getMessage(),
                [
                    'data' => $data,
                    'styleMode' => $infoblocksOptions['style']

                ]
            );
        }
    }

    protected function getInfoblocks($infoblocksOptions, $complect, $section, $paragraphStyle)
    {

        $totalCount = $this->getInfoblocksCount($complect);
        $headingStyle = ['bold' => true, 'size' => 16, 'name' => 'Arial'];
        $textStyle = ['size' => 12, 'name' => 'Arial'];
        $textStyleBold = ['size' => 12, 'name' => 'Arial', 'bold' => true, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER];
        $textStyleSmall = ['size' => 10, 'name' => 'Arial'];
        $textStyleSmallBold = ['size' => 10, 'name' => 'Arial', 'bold' => true];

        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];

        $section->addText('Информационное наполнение', $textStyleBold);
        $section->addTextBreak(1);


        if ($styleMode == 'list') {
            foreach ($complect as $group) {

                // $section->addText($group['groupsName'], $headingStyle);
                // $section->addTextBreak(1);
                foreach ($group['value'] as $infoblock) {

                    if (array_key_exists('code', $infoblock)) {
                        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();

                        if ($currentInfoblock) {

                            if ($descriptionMode === 0) {
                                $section->addText($currentInfoblock['name'], $textStyleSmall, $paragraphStyle);
                            } else   if ($descriptionMode === 1) {
                                $section->addText($currentInfoblock['name'], $textStyleSmallBold, $paragraphStyle);
                                $section->addText($currentInfoblock['shortDescription'], $textStyleSmall, $paragraphStyle);
                                $section->addTextBreak(1);
                            } else   if ($descriptionMode === 2) {
                                $section->addText($currentInfoblock['name'], $textStyleSmallBold, $paragraphStyle);
                                $section->addText($currentInfoblock['descriptionForSale'], $textStyleSmall, $paragraphStyle);
                                $section->addTextBreak(1);
                            } else   if ($descriptionMode === 3) {
                                $section->addText($currentInfoblock['name'], $textStyleSmall, $paragraphStyle);
                                $section->addText($currentInfoblock['descriptionForSale'], $textStyleSmall, $paragraphStyle);
                                $section->addTextBreak(1);
                            }
                        }
                    }
                }
            }
        } else if ($styleMode == 'table') {




            $fullWidth = $section->getStyle()->getPageSizeW();
            $marginRight = $section->getStyle()->getMarginRight();
            $marginLeft = $section->getStyle()->getMarginLeft();
            $contentWidth = ($fullWidth - $marginLeft - $marginRight) / 2;
            $innerContentWidth = ($fullWidth - $marginLeft - $marginRight) - 30;
            $innerCellStyle = [
                'borderSize' => 0,
                'borderColor' => 'FFFFFF',
                'cellMargin' => 40,
                'valign' => 'top',

                // 'cellSpacing' => 10

            ];
            $innerTabletyle = [
                'borderSize' => 0,
                'borderColor' => 'FFFFFF',
                'cellMargin' => 40,

                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
                // 'cellSpacing' => 10

            ];
            $fancyTableStyleName = 'TableStyle';
            $fancyTableStyle = [
                'borderSize' => 10,
                'borderColor' => '000000',
                'cellMargin' => 40,
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                'cellSpacing' => 10
            ];
            // 
            $fancyTableFirstRowStyle = ['cellMargin' => 90, 'borderSize' => 0, 'bgColor' => '66BBFF', 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,]; //,'borderColor' => '000000'
            // $fancyTableCellStyle = ['valign' => 'center'];
            // $fancyTableCellBtlrStyle = ['valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR];
            // $fancyTableFontStyle = ['bold' => true,];

            $fancyTableCellStyle = [
                'valign' => 'top',
                'borderSize' => 6,
                // 'borderColor' => '000000',  // Цвет границы (чёрный)
                'cellMarginTop' => 100,
                'cellMarginRight' => 100,
                'cellMarginBottom' => 100,
                'cellMarginLeft' => 100,
            ];

            $section->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
            $table = $section->addTable($fancyTableStyleName);
            $table->addRow();
            $cell = $table->addCell($contentWidth, $fancyTableCellStyle);

            $innerTable = $cell->addTable($innerTabletyle);
            $innerTable->addRow();
            $innerTableCell = $innerTable->addCell($innerContentWidth, $innerCellStyle); // Уменьшаем ширину, чтобы создать отступ




            $count = 0;
            $isTwoColExist = false;
            foreach ($complect as $group) {
                // $table->addCell($contentWidth, $fancyTableCellStyle)->addText($group['groupsName'], $headingStyle);


                foreach ($group['value'] as $infoblock) {

                    if (array_key_exists('code', $infoblock)) {
                        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();

                        if ($currentInfoblock) {


                            if ($count < ($totalCount['infoblocks'] / 2)) {

                                $this->addInfoblockToCell($styleMode, $innerTableCell, $currentInfoblock, $descriptionMode, $paragraphStyle);
                            } else {
                                // Если count нечетный, добавляем вторую ячейку в текущую строку
                                if (!$isTwoColExist) {
                                    $cell = $table->addCell($contentWidth, $fancyTableCellStyle);
                                    $innerTable = $cell->addTable($innerTabletyle);
                                    $innerTable->addRow();
                                    $innerTableCell = $innerTable->addCell($innerContentWidth, $innerCellStyle); // Уменьшаем ширину, чтобы создать отступ
                                    $isTwoColExist = true;
                                }

                                $this->addInfoblockToCell($styleMode, $innerTableCell, $currentInfoblock, $descriptionMode, $paragraphStyle);
                            }
                            // $section->addTextBreak(1);
                            $count = $count  + 1;
                        }
                    }
                }
            }
        } else if ($styleMode == 'tableWithGroup') {

            $fullWidth = $section->getStyle()->getPageSizeW();
            $marginRight = $section->getStyle()->getMarginRight();
            $marginLeft = $section->getStyle()->getMarginLeft();
            $contentWidth = ($fullWidth - $marginLeft - $marginRight - 100);
            $textTableGroupTitle = [
                'size' => 10,
                'name' => 'Arial',
                'bold' => true,

            ];
            $textTableGroupTitleParagraph = [
                'spaceAfter' => 0,    // Интервал после абзаца
                'spaceBefore' => 0,   // Интервал перед абзацем
                'lineHeight' => 1.15,  // Высота строки
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                'valign' => 'center',
            ];
            $innerCellStyle = [
                'borderSize' => 0,
                'borderColor' => 'FFFFFF',
                'cellMargin' => 10,
                'valign' => 'center',
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,

                // 'cellSpacing' => 10

            ];
            $innerTabletyle = [
                'borderSize' => 0,
                'borderColor' => 'FFFFFF',
                'cellMargin' => 40,

                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
                // 'cellSpacing' => 10

            ];
            $fancyTableStyleName = 'TableStyle';
            $fancyTableStyle = [
                'borderSize' => 10,
                'borderColor' => '000000',
                'cellMargin' => 40,
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                'cellSpacing' => 10
            ];
            // 
            $fancyTableFirstRowStyle = ['cellMargin' => 90, 'borderSize' => 0, 'bgColor' => '66BBFF', 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,]; //,'borderColor' => '000000'
            // $fancyTableCellStyle = ['valign' => 'center'];
            // $fancyTableCellBtlrStyle = ['valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR];
            // $fancyTableFontStyle = ['bold' => true,];

            $fancyTableCellStyle = [
                'valign' => 'center',
                'borderSize' => 6,
                // 'borderColor' => '000000',  // Цвет границы (чёрный)
                'cellMarginTop' => 10,
                'cellMarginRight' => 10,
                'cellMarginBottom' => 10,
                'cellMarginLeft' => 10,
            ];

            $section->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
            $table = $section->addTable($fancyTableStyleName);





            $count = 0;
            $isTwoColExist = false;
            foreach ($complect as $group) {
                // $table->addCell($contentWidth, $fancyTableCellStyle)->addText($group['groupsName'], $headingStyle);
                $table->addRow();
                $cell = $table->addCell($contentWidth, $fancyTableCellStyle);

                $innerTable = $cell->addTable($innerTabletyle);
                $innerTable->addRow();
                $innerTableCell = $innerTable->addCell($contentWidth, $innerCellStyle);
                $innerTableCell->addText($group['groupsName'], $textTableGroupTitle, $textTableGroupTitleParagraph);

                foreach ($group['value'] as $infoblock) {

                    if (array_key_exists('code', $infoblock)) {
                        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();

                        if ($currentInfoblock) {
                            $table->addRow();
                            $cell = $table->addCell($contentWidth, $fancyTableCellStyle);

                            $innerTable = $cell->addTable($innerTabletyle);
                            $innerTable->addRow();
                            $innerTableCell = $innerTable->addCell($contentWidth, $innerCellStyle); // Уменьшаем ширину, чтобы создать отступ
                            $isTwoColExist = true;


                            $this->addInfoblockToCell($styleMode, $innerTableCell, $currentInfoblock, $descriptionMode, $paragraphStyle);

                            // $section->addTextBreak(1);
                            $count = $count  + 1;
                        }
                    }
                }
            }
        }


        if ($descriptionMode === 3) {
            $section = $this->getDescriptionPage(
                $complect,
                $section,
                $headingStyle,
                $textStyle,
                $textStyleBold
            );
        }



        return $section;
    }

    protected function getDescriptionPage(
        $complect,
        $section,
        $headingStyle,
        $textStyle,
        $textStyleBold
    ) {
        $section->addPageBreak();
        foreach ($complect as $group) {
            // $section->addTextBreak(1);
            $section->addText($group['groupsName'], $headingStyle);
            $section->addTextBreak(1);
            foreach ($group['value'] as $infoblock) {

                if (array_key_exists('code', $infoblock)) {
                    $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();




                    if ($currentInfoblock) {
                        $section->addText($currentInfoblock['name'], $textStyleBold);

                        $section->addText($currentInfoblock['description'], $textStyle);


                        $section->addTextBreak(1);
                    }
                }
            }
        }
        return $section;
    }

    protected function getInfoblocksCount($complect)
    {
        $result = [
            'groups' => 0,
            'infoblocks' => 0
        ];


        foreach ($complect as $group) {
            $result['groups'] += 1;

            foreach ($group['value'] as $infoblock) {
                $result['infoblocks'] += 1;
            }
        }
        return  $result;
    }

    protected function addInfoblockToCell($tableType, $cell, $infoblock, $descriptionMode)
    {
        // Создаем стиль абзаца
        $paragraphStyle = array(
            'spaceAfter' => 0,    // Интервал после абзаца
            'spaceBefore' => 0,   // Интервал перед абзацем
            'lineHeight' => 1.15,  // Высота строки
            // Другие параметры стиля абзаца...
        );
        $paragraphTitleStyle = array(
            'spaceAfter' => 1,    // Интервал после абзаца
            'spaceBefore' => 0,   // Интервал перед абзацем
            'lineHeight' => 1.5,  // Высота строки
            // Другие параметры стиля абзаца...
        );

        $textStyleSmall = ['size' => 10, 'name' => 'Arial'];
        $textStyleSmallBold = ['size' => 10, 'name' => 'Arial', 'bold' => true];

        switch ($descriptionMode) {
            case 0:
                $cell->addText($infoblock['name'], $textStyleSmall, $paragraphStyle);
                break;
            case 1:
                $cell->addText($infoblock['name'], $textStyleSmallBold, $paragraphTitleStyle);
                $cell->addText($infoblock['shortDescription'], $textStyleSmall, $paragraphStyle);
                if ($tableType == 'table') {
                    $cell->addTextBreak(1);
                }

                break;
            case 2:
            case 3:
                $cell->addText($infoblock['name'], $textStyleSmallBold, $paragraphTitleStyle);
                $cell->addText($infoblock['descriptionForSale'], $textStyleSmall, $paragraphStyle);
                if ($tableType == 'table') {
                    $cell->addTextBreak(1);
                }
                break;
        }
    }

    protected function getPriceSection($section, $price)
    {
        try {

            //стиль страницы 
            //    $sectionStyle = array(
            //     'pageSizeW' => Converter::inchToTwip(8.5), // ширина страницы
            //     'pageSizeH' => Converter::inchToTwip(11),   // высота страницы
            //     'marginLeft' => Converter::inchToTwip(0.5),
            //     'marginRight' => Converter::inchToTwip(0.5),
            //     'lang' => 'ru-RU',
            //     'heading' => ['bold' => true, 'size' => 16, 'name' => 'Arial'],
            //     'text' => ['size' => 12, 'name' => 'Arial'],
            //     'textSmall' => ['size' => 10, 'name' => 'Arial'],
            //     'textSmallBold' => ['size' => 10, 'name' => 'Arial', 'bold' => true],
            //     'textBold' => ['size' => 12, 'name' => 'Arial', 'bold' => true],

            // );



            //ТАБЛИЦА ЦЕН
            $priceDataGeneral = null;
            $priceDataAlternative = null;
            $priceDataTotal = null;
            $isHaveLongPrepayment = false;
            if (isset($price['cells']['general']) && is_array($price['cells']['general']) && count($price['cells']['general']) > 0) {
                // Массив $price['cells']['general'] существует и не пуст
                $priceDataGeneral = $price['cells']['general'][0]['cells'];
            }

            if (isset($price['cells']['alternative']) && is_array($price['cells']['alternative']) && count($price['cells']['alternative']) > 0) {
                // Массив $price['cells']['alternative'] существует и не пуст
                $priceDataAlternative = $price['cells']['alternative'][0]['cells'];
            }

            if (isset($price['cells']['total']) && is_array($price['cells']['total']) && count($price['cells']['total']) > 0) {
                // Массив $price['cells']['total'] существует и не пуст
                $priceDataTotal = $price['cells']['total'][0]['cells'];
            }




            $cells = [];
            $isTable = $price['isTable'];

            // $header = ['size' => 16, 'bold' => true];

            // $headerRqStyle = ['valign' => 'left'];
            // $header->addText($combinedRq, $headerRqStyle);
            // $header->addImage('path_to_logo.jpg', $logoStyle);
            $languageEnGbStyle = array('lang' => 'ru-RU');
            $section->addTextBreak(1);
            $section->addText('Цена за комплект', $languageEnGbStyle);


            $fancyTableStyleName = 'DocumentPrice';



            // Создаем стиль абзаца
            $paragraphStyle = array(
                'spaceAfter' => 0,    // Интервал после абзаца
                'spaceBefore' => 0,   // Интервал перед абзацем
                'lineHeight' => 1.15,  // Высота строки
                // Другие параметры стиля абзаца...
            );
            $fullWidth = $section->getStyle()->getPageSizeW();
            $marginRight = $section->getStyle()->getMarginRight();
            $marginLeft = $section->getStyle()->getMarginLeft();
            $contentWidth = $fullWidth - $marginLeft - $marginRight;


            //TABLE
            $allPrices = [$price['cells']['general'], $price['cells']['alternative'], $price['cells']['total']];
            // usort($price['cells']['general'], function ($a, $b) {
            //     return $a->order - $b->order;
            // });
            // usort($price['cells']['alternative'], function ($a, $b) {
            //     return $a->order - $b->order;
            // });

            //SORT CELLS
            // foreach ($allPrices as $target) {
            //     if ($target) {
            //         foreach ($target as $product) {
            //             if ($product && $product['cells']) {
            //                 usort($product['cells'], function ($a, $b) {
            //                     return $a->order - $b->order;
            //                 });
            //             }
            //         }
            //     }
            // }
            if ($isTable) {

                // Расчет ширины каждой ячейки в зависимости от количества столбцов
                if ($priceDataGeneral) {
                    $activePriceCellsGeneral = array_filter($priceDataGeneral, function ($prc) {
                        return $prc['isActive'];
                    });
                    foreach ($activePriceCellsGeneral as $prccll) {
                        if (($prccll['code'] == 'contractquantity' && $prccll['isActive']) ||
                            ($prccll['code'] == 'prepayment' && $prccll['isActive'])
                        ) {
                            $isHaveLongPrepayment = true; // Установить в true, если условие выполнено
                            break; // Прекратить выполнение цикла, так как условие уже выполнено
                        }
                    }
                   
                }
                if ($priceDataAlternative) {
                    $activePriceCellsAlternative = array_filter($priceDataAlternative, function ($prc) {
                        return $prc['isActive'];
                    });
                }

                if ($activePriceCellsGeneral) {
                    $numCells = count($activePriceCellsGeneral); // Количество столбцов
                    $cellWidth = $contentWidth / $numCells;
                    $outerFirstWidth =  $cellWidth + 1000;
                    // $innerFirstWidth = $outerFirstWidth - 30;
                    // $outerWidth =  $cellWidth - (1000 / $numCells);
                    // $innerWidth = $outerWidth - 30;

                    // $innerCellStyle = [
                    //     'borderSize' => 0,
                    //     'borderColor' => 'FFFFFF',
                    //     'cellMargin' => 10,
                    //     'valign' => 'center',
                    //     'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                    //     // 'cellSpacing' => 10

                    // ];

                    // $innerTabletyle = [
                    //     'borderSize' => 0,
                    //     'borderColor' => 'FFFFFF',
                    //     'cellMargin' => 10,
                    //     'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
                    //     // 'cellSpacing' => 10

                    // ];
                    $fancyTableStyleName = 'TableStyle';
                    $fancyTableStyle = [
                        'borderSize' => 10,
                        'borderColor' => '000000',
                        'cellMargin' => 10,
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellSpacing' => 10
                    ];
                    // 
                    $fancyTableFirstRowStyle = ['cellMargin' => 90, 'borderSize' => 0, 'bgColor' => '66BBFF', 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER]; //,'borderColor' => '000000'


                    // $fancyTableCellStyle = [
                    //     'valign' => 'center',
                    //     'borderSize' => 6,
                    //     // 'borderColor' => '000000',  // Цвет границы (чёрный)
                    //     'cellMarginTop' => 10,
                    //     'cellMarginRight' => 10,
                    //     'cellMarginBottom' => 10,
                    //     'cellMarginLeft' => 10,
                    // ];
                    // $fancyTableCellBtlrStyle = ['valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR];
                    // $fancyTableFontStyle = ['bold' => true];
                    // $textTableGroupTitleParagraphFirst =  [
                    //     'spaceAfter' => 0,    // Интервал после абзаца
                    //     'spaceBefore' => 0,   // Интервал перед абзацем
                    //     'lineHeight' => 1.15,  // Высота строки
                    //     'alignment' => 'left',
                    //     'valign' => 'center',
                    // ];

                    // $textTableGroupTitleParagraph =  [
                    //     'spaceAfter' => 0,    // Интервал после абзаца
                    //     'spaceBefore' => 0,   // Интервал перед абзацем
                    //     'lineHeight' => 1.15,  // Высота строки
                    //     'alignment' => 'center',
                    //     'valign' => 'center',
                    // ];

                    // $textTableGroupTitleParagraphLast =  [
                    //     'spaceAfter' => 0,    // Интервал после абзаца
                    //     'spaceBefore' => 0,   // Интервал перед абзацем
                    //     'lineHeight' => 1.15,  // Высота строки
                    //     'alignment' => 'right',
                    //     'valign' => 'center',
                    // ];
                    $section->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                    $table = $section->addTable($fancyTableStyleName);
                    $table->addRow();
                    $totalWidth = 0;
                    $count = 0;
                    foreach ($activePriceCellsGeneral as $index => $priceCell) {

                        Log::info('index', ['index' => $index]);

                        $this->getPriceCell($table, $totalWidth, $priceCell, $contentWidth, $isHaveLongPrepayment, $numCells);
                        $count += 1;
                        // if ($index == 0 || $index === '0') {

                        //     $cell = $table->addCell($outerFirstWidth, $fancyTableCellStyle);
                        //     $innerTable = $cell->addTable($innerTabletyle);
                        //     $innerTable->addRow();
                        //     $innerTableCell = $innerTable->addCell($innerFirstWidth, $innerCellStyle)
                        //         ->addText($priceCell['name'], $fancyTableFontStyle, $textTableGroupTitleParagraphFirst);
                        // } else {

                        //     $cell = $table->addCell($outerWidth, $fancyTableCellStyle);
                        //     $innerTable = $cell->addTable($innerTabletyle);
                        //     $innerTable->addRow();
                        //     $innerTableCell = $innerTable->addCell($innerWidth, $innerCellStyle)
                        //         ->addText($priceCell['name'], $fancyTableFontStyle, $textTableGroupTitleParagraph);
                        // }
                    }
                    // $table->addRow();
                    // foreach ($price['cells']['general'] as  $prc) {
                    //     foreach ($prc['cells'] as $cll) {

                    //         if ($cll['isActive']) {


                    //             if ($cll['code'] == 'name') {
                    //                 $outerWidth =  $cellWidth +  100;
                    //                 $innerWidth = $cellWidth + 90;
                    //                 $value = $cll['code']  === "discountprecent" ? round((100 -  $cll['value'] * 100), 2) : $cll['value'];
                    //                 $cell = $table->addCell($outerWidth, $fancyTableCellStyle);
                    //                 $innerTable = $cell->addTable($innerTabletyle);
                    //                 $innerTable->addRow();
                    //                 $innerTableCell = $innerTable->addCell($innerWidth, $innerCellStyle)
                    //                     ->addText($value, $fancyTableFontStyle, $textTableGroupTitleParagraphFirst);
                    //             } else {
                    //                 $outerWidth =  $cellWidth - 20;
                    //                 $innerWidth = $cellWidth - 30;
                    //                 $value = $cll['code']  === "discountprecent" ? round((100 -  $cll['value'] * 100), 2) : $cll['value'];
                    //                 $cell = $table->addCell($outerWidth, $fancyTableCellStyle);
                    //                 $innerTable = $cell->addTable($innerTabletyle);
                    //                 $innerTable->addRow();
                    //                 $innerTableCell = $innerTable->addCell($innerWidth, $innerCellStyle)
                    //                     ->addText($value, $fancyTableFontStyle, $textTableGroupTitleParagraph);
                    //             }
                    //         }
                    //     }
                    // }

                    // if ($priceDataAlternative) {

                    //     foreach ($price['cells']['alternative'] as $prc) {
                    //         $table->addRow();
                    //         foreach ($prc['cells'] as $cll) {
                    //             if ($cll['isActive']) {


                    //                 if ($cll['code'] == 'name') {
                    //                     $value = $cll['code']  === "discountprecent" ? round((100 -  $cll['value'] * 100), 2) : $cll['value'];
                    //                     $cell = $table->addCell($cellWidth + 100, $fancyTableCellStyle);
                    //                     $innerTable = $cell->addTable($innerTabletyle);
                    //                     $innerTable->addRow();
                    //                     $innerTableCell = $innerTable->addCell($contentWidth + 90, $innerCellStyle)
                    //                         ->addText($value, $fancyTableFontStyle, $textTableGroupTitleParagraphFirst);
                    //                 } else {
                    //                     $value = $cll['code']  === "discountprecent" ? round((100 -  $cll['value'] * 100), 2) : $cll['value'];
                    //                     $cell = $table->addCell($cellWidth, $fancyTableCellStyle);
                    //                     $innerTable = $cell->addTable($innerTabletyle);
                    //                     $innerTable->addRow();
                    //                     $innerTableCell = $innerTable->addCell($contentWidth - 30, $innerCellStyle)
                    //                         ->addText($value, $fancyTableFontStyle, $textTableGroupTitleParagraph);
                    //                 }
                    //             }
                    //         }
                    //     }
                    // }
                }
            } else {

                //NOTABLE PRICE
                $section->addTextBreak(1);

                // Сортировка массива

                $measure = null;
                foreach ($price['cells']['general'] as $prc) {
                    $text = '';
                    foreach ($prc['cells'] as $cll) {
                        if ($cll['code'] === 'measure') {
                            $measure = $cll['value'];
                            break; // Прерываем цикл, как только найдем нужный элемент
                        }
                    }
                }



                $texts = [];
                $values = [];
                $quantity = null;
                $clls = [];
                $allPrices = [];
                $allPrices[] = $price['cells']['general'];
                $allPrices[] = $price['cells']['alternative'];
                // Создание стилей
                $boldStyle = array('bold' => true);
                $colorStyle = array('bold' => true, 'color' => 'FF0000'); // Красный цвет
                $section->addFontStyle('BoldText', array('bold' => true));
                $section->addFontStyle('ColorBoldText', array('bold' => true, 'color' => 'FF0000'));
                if ($measure) {
                    foreach ($allPrices as $target) {

                        foreach ($target as $prc) {
                            $text = '';

                            $textRunBold = $section->addTextRun('ColorBoldText');
                            $isQantityNamed = false;
                            $isQantityPrepaymentNamed = false;

                            $isSumNamed = false;
                            $isSumPrepaymentNamed = false;
                            $isSumContractNamed = false;
                            $string = '';
                            foreach ($prc['cells'] as $cll) {
                                $value = '';
                                $applyStyle = null;
                                // $textRun = $section->addTextRun($languageEnGbStyle);
                                $isNamed = false;

                                if ($cll['isActive'] && !empty(trim($cll['value']))) {

                                    if (
                                        $cll['name'] === 'При заключении договора от' ||
                                        $cll['name'] === 'При внесении предоплаты от'


                                    ) {

                                        $isNamed = $cll;
                                        $clls[] = $cll;
                                        $value = $cll['name'] . ' ';

                                        $texts[] = $value;
                                    } else if (

                                        $cll['name'] === 'Сумма за весь период обслуживания' ||
                                        $cll['name'] === 'Сумма предоплаты' ||
                                        $cll['name'] === 'Сумма'


                                    ) {
                                        $length = strlen($string);
                                        if ($length > 75) {
                                            $textRunBold->addTextBreak();
                                        }

                                        $isNamed = $cll;
                                        $clls[] = $cll;
                                        $value = $cll['name'];
                                        $texts[] = $value;
                                    } else if (

                                        $cll['name'] === 'Цена в месяц' ||
                                        $cll['name'] === 'Цена'

                                    ) {
                                        $isNamed = $cll;
                                        $clls[] = $cll;
                                        $value = $cll['name'];

                                        $texts[] = $value;
                                    } else if ($cll['code'] === 'quantity') {
                                        $value = FileController::getFullMeasureValue($measure, $cll['value']) . '';
                                        $texts[] = $value;
                                        $quantity =  $cll['value'];
                                    } else if ($cll['code']  === "discountprecent") {
                                        $value =  round((100 -  $cll['value'] * 100), 2) . '';
                                        $texts[] = $value;
                                    } else if ($cll['code']  === "measure") {
                                        // $value = $cll['value'] . '';
                                        $texts[] = $value;
                                    } else {

                                        $value = $cll['value'] . '';
                                        $texts[] = $value;
                                    }

                                    if ($isNamed) {
                                        $text = $text . '  ' . $value;
                                        if ((
                                            $cll['name'] === 'Сумма за весь период обслуживания' ||
                                            $cll['name'] === 'Сумма предоплаты' ||
                                            $cll['name'] === 'Сумма'
                                        ) && $length > 75) {
                                            $textRunBold->addText($value, $boldStyle);
                                            $textRunBold->addText('  ' . $isNamed['value'], $colorStyle);
                                        } else {
                                            $textRunBold->addText('  ' . $value, $boldStyle);
                                            $textRunBold->addText('  ' . $isNamed['value'], $colorStyle);
                                        }

                                        $string = $string . '  ' . $value;
                                        $string = $string . '  ' . $isNamed['value'];
                                    } else {
                                        if (empty(trim($text))) {
                                            $text = $value;
                                            $string = $string . $value;
                                            $textRunBold->addText($value,  $boldStyle);
                                        } else {
                                            $text = $text . '  ' . $value;
                                            $string = $string . '  ' . $value;
                                            $textRunBold->addText('  ' . $value, $boldStyle);
                                        }
                                    }





                                    // Добавляем текст с соответствующим стилем
                                    // $text = $cll['name'] . ' ' . $cll['value'] . ' ';

                                }
                                // // Добавляем перенос строки после обработки всех ячеек в строке

                            }
                            // $textRunBold->addText($text);
                            // $section->addText($text);
                            // if ($applyStyle) {
                            //     $section->addText($text, $applyStyle);
                            // } else {
                            //     $section->addText($text, $languageEnGbStyle);
                            // }
                        }
                    }
                }
            }
            return $section;
        } catch (\Throwable $th) {
            return [
                'resultCode' => 1,
                'result' => null,
                'message' => $th->getMessage()
            ];
        }
    }

    protected function getPriceCell($table, $totalWidth, $priceCell, $contentWidth, $isHaveLongPrepayment, $allCellsCount)
    {
        $code = $priceCell['code'];
        // NAME = 'name',     
        // PREPAYMENT = 'prepayment',
        // QUANTITY = 'quantity',
        // DEFAULT_QUANTITY = 'defaultquantity',
        // CONTRACT_QUANTITY = 'contractquantity',
        // DISCOUNT_PRECENT = 'discountprecent',
        // DISCOUNT_AMOUNT = 'discountamount',
        // DEFAULT = 'default',
        // CURRENT = 'current',
        // DEFAULT_MONTH = 'defaultmonth',
        // CURRENT_MONTH = 'currentmonth',
        // PREPAYMENT_SUM = 'prepaymentsum',
        // QUANTITY_SUM = 'quantitysum',
        // CONTRACT_SUM = 'contractsum',
        // MEASURE = 'measure',
        // MEASURE_CODE = 'measureCode',
        // CONTRACT = 'contract',
        // SUPPLY = 'supply',
        // SUPPLY_FOR_OFFER = 'supplyForOffer',
        // вычислить длину ячейки 100
        // NAME = 'Наименование',
        // QUANTITY = 'Количество',
        // SUM = 'Сумма',
        // DEFAULT_QUANTITY = 'Количество изначальное',
        // DISCOUNT_PRECENT = 'Скидка, %',
        // DISCOUNT_AMOUNT = 'Скидка в рублях',
        // DEFAULT = 'Цена по прайсу',
        // CURRENT = 'Цена',
        // DEFAULT_MONTH = 'Цена по прайсу в месяц',
        // CURRENT_MONTH = 'Цена в месяц',
        // QUANTITY_SUM = 'Сумма Количество',
        // CONTRACT_QUANTITY = 'При заключении договора от',
        // PREPAYMENT_QUANTITY = 'При внесении предоплаты от',
        // CONTRACT_SUM = 'Сумма за весь период обслуживания',
        // PREPAYMENT_SUM = 'Сумма предоплаты',
        // MEASURE = 'Единица',
        // MEASURE_CODE = 'Кодовое обозначение единицы',
        // CONTRACT = 'contract',
        // SUPPLY = 'Количество доступов',
        // SUPPLY_FOR_OFFER = 'Версия',
        $longWidth = 7000;
        if ($isHaveLongPrepayment) {
            $longWidth = 3600;
        }
        $cellWidth = ($contentWidth - $longWidth) / $allCellsCount;
        $outerWidth =  $cellWidth;
        $innerWidth = $outerWidth - 30;
        // $outerWidth =  $cellWidth - (1000 / $cellsCount);
        // $innerWidth = $outerWidth - 30;
        $fancyTableCellStyle = [
            'valign' => 'center',
            'borderSize' => 6,
            // 'borderColor' => '000000',  // Цвет границы (чёрный)
            'cellMarginTop' => 10,
            'cellMarginRight' => 10,
            'cellMarginBottom' => 10,
            'cellMarginLeft' => 10,
        ];

        $innerTabletyle = [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 10,
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
            // 'cellSpacing' => 10

        ];
        $innerCellStyle = [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 230,
            'valign' => 'center',
            'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            'cellMarginTop' => 50,
            'cellMarginRight' => 50,
            'cellMarginBottom' => 50,
            'cellMarginLeft' => 50,

        ];


        $fancyTableFontStyle = ['bold' => true, 'lang' => 'ru-RU',];


        $textTableGroupTitleParagraph =  [
            'spaceAfter' => 0,    // Интервал после абзаца
            'spaceBefore' => 0,   // Интервал перед абзацем
            'lineHeight' => 1.15,  // Высота строки
            'alignment' => 'center',
            'valign' => 'center',
        ];

        if ($code) {


            switch ($code) {
                case 'name':  //Наименование
                    // $textTableGroupTitleParagraph =  [
                    //     'spaceAfter' => 0,    // Интервал после абзаца
                    //     'spaceBefore' => 0,   // Интервал перед абзацем
                    //     'lineHeight' => 1.15,  // Высота строки
                    //     'alignment' => 'left',
                    //     'valign' => 'center',
                    // ];
                    $outerWidth =  $cellWidth + 2000;
                    $innerWidth = $outerWidth - 30;

                case 'quantity': //Количество
                    # code...

                case 'defaultquantity': //Количество изначальное
                    # code...

                case 'contractquantity': //При заключении договора от
                    $outerWidth =  $cellWidth + 800;
                    $innerWidth = $outerWidth - 30;

                case 'prepayment':  // При внесении предоплаты от
                    $outerWidth =  $cellWidth + 800;
                    $innerWidth = $outerWidth - 30;


                case 'discountprecent': //Скидка, %
                    # code...

                case 'discountamount': //Скидка в рублях
                    # code...


                case 'current': //Цена
                    # code...

                case 'currentmonth': //Цена в месяц
                    # code...

                case 'default': //Цена по прайсу
                    # code...


                case 'defaultmonth': //Цена по прайсу в месяц
                    # code...

                case 'quantitysum': //Сумма Количество
                    # code...

                case 'contractsum': //Сумма за весь период обслуживания 
                    $outerWidth =  $cellWidth + 800;
                    $innerWidth = $outerWidth - 30;

                case 'prepaymentsum':  // При внесении предоплаты от
                    $outerWidth =  $cellWidth + 800;
                    $innerWidth = $outerWidth - 30;

                case 'measure': //Единица
                    # code...

                case 'measureCode': //Кодовое обозначение единицы
                    # code...

                case 'contract':
                    # code...

                case 'supply':
                    # code...

                case 'supplyOffer':
                    # code...

            }


            $totalWidth =  $totalWidth + $outerWidth;
            $cell = $table->addCell($outerWidth, $fancyTableCellStyle);
            $innerTable = $cell->addTable($innerTabletyle);
            $innerTable->addRow();
            $innerTableCell = $innerTable->addCell($innerWidth, $innerCellStyle)
                ->addText($priceCell['name'], $fancyTableFontStyle, $textTableGroupTitleParagraph);
        }
        return $table;
    }
}
