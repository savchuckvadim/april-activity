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
    protected $documentStyle;

    public function __construct()
    {
        $generalFont = [
            'name' => 'Arial',
            'color' => '0000FF',
            'lang' => 'ru-RU',
        ];
        $this->documentStyle = [
            'page' => [
                'pageSizeW' => Converter::inchToTwip(8.5), // ширина страницы
                'pageSizeH' => Converter::inchToTwip(11),   // высота страницы
                'marginLeft' => Converter::inchToTwip(0.5),
                'marginRight' => Converter::inchToTwip(0.5),
            ],

            'colors' => [

                'general' => '0000FF',
                'second' => '0000FF',
                'white' => '0000FF',
                'shadow' => '0000FF',
                'atention' => '0000FF',
                'shadowText' => '0000FF'


            ],

            'fonts' => [
                'general' => [
                    'name' => 'Arial',
                    'color' => '0000FF',
                    'lang' => 'ru-RU',
                ],
                'h1' => [
                    ...$generalFont,
                    'bold' => true, 'size' => 16
                ],
                'h2' => [
                    ...$generalFont,
                    'bold' => true, 'size' => 14
                ],
                'h3' => [
                    ...$generalFont,
                    'bold' => true, 'size' => 12
                ],
                'text' => [
                    'small' => [
                        ...$generalFont,
                        'size' => 9
                    ],
                    'normal' => [
                        ...$generalFont,
                        'size' => 10
                    ],
                    'bold' => [
                        ...$generalFont,
                        'bold' => true,
                        'size' => 10
                    ],

                ],
                'table' => [
                    'h1' => [
                        ...$generalFont,
                        'size' => 11,  'bold' => true, 'lang' => 'ru-RU'
                    ],
                    'h2' => [
                        ...$generalFont,
                        'size' => 10,  'bold' => true, 'lang' => 'ru-RU'
                    ],
                    'text' => [
                        ...$generalFont,
                        'size' => 10, 'lang' => 'ru-RU'
                    ]
                ]
            ],
            'paragraphs' => [
                'general' => [
                    'valign' => 'center',
                    'spaceAfter' => 0,    // Интервал после абзаца
                    'spaceBefore' => 0,   // Интервал перед абзацем
                    'lineHeight' => 1.15,  // Высота строки
                ],
                'head' => [
                    'valign' => 'center',
                    'spaceAfter' => 1,    // Интервал после абзаца
                    'spaceBefore' => 0,   // Интервал перед абзацем
                    'lineHeight' => 1.5,  // Высота строки
                ],
                'align' => [
                    'left' => [
                        'alignment' => 'left',

                    ],
                    'right' => [
                        'alignment' => 'right',

                    ],
                    'center' => [
                        'alignment' => 'center',

                    ]



                ],


            ],
            'tables' => [
                'inner' => [
                    'cell' => [
                        // 'infoblock' => [
                        //     'borderSize' => 0,
                        //     'borderColor' => 'FFFFFF',
                        //     'cellMargin' => 40,
                        //     'valign' => 'center',
                        //     // 'cellSpacing' => 10
                        // ],
                        // 'price' => [
                        'borderSize' => 0,
                        'borderColor' => 'FFFFFF',
                        'cellMargin' => 230,
                        'valign' => 'center',
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellMarginTop' => 50,
                        'cellMarginRight' => 50,
                        'cellMarginBottom' => 50,
                        'cellMarginLeft' => 50,
                        // ]

                    ],
                    'table' => [
                        'borderSize' => 0,
                        'borderColor' => 'FFFFFF',
                        'cellMargin' => 40,
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER


                    ],

                ],

                'general' => [
                    'table' => [
                        'borderSize' => 10,
                        'borderColor' => '000000',
                        'cellMargin' => 40,
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellSpacing' => 10
                    ],
                    'row' => [
                        'cellMargin' => 90, 'borderSize' => 0, 'bgColor' => '66BBFF', 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
                    ],
                    'cell' => [
                        'valign' => 'center',
                        'borderSize' => 6,
                        // 'borderColor' => '000000',  // Цвет границы (чёрный)
                        'cellMarginTop' => 10,
                        'cellMarginRight' => 10,
                        'cellMarginBottom' => 10,
                        'cellMarginLeft' => 10,
                    ],
                    'paragraphs' => [
                        'left' => [
                            'spaceAfter' => 0,    // Интервал после абзаца
                            'spaceBefore' => 0,   // Интервал перед абзацем
                            'lineHeight' => 1.15,  // Высота строки
                            'alignment' => 'left',
                            'valign' => 'center',
                        ],
                        'center' => [
                            'spaceAfter' => 0,    // Интервал после абзаца
                            'spaceBefore' => 0,   // Интервал перед абзацем
                            'lineHeight' => 1.15,  // Высота строки
                            'alignment' => 'center',
                            'valign' => 'center',
                        ],
                        'right' => [
                            'spaceAfter' => 0,    // Интервал после абзаца
                            'spaceBefore' => 0,   // Интервал перед абзацем
                            'lineHeight' => 1.15,  // Высота строки
                            'alignment' => 'right',
                            'valign' => 'center',
                        ]
                    ]
                ]
            ]

        ];
    }


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



            $document = new \PhpOffice\PhpWord\PhpWord();



            $section = $document->addSection($this->documentStyle['page']);
            $this->getPriceSection($section, $this->documentStyle,  $data['price']);
            $this->getInfoblocks($infoblocksOptions, $complect, $section, $this->documentStyle);

            // //СОХРАНЕНИЕ ДОКУМЕТА
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document, 'Word2007');
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

    protected function getInfoblocks($infoblocksOptions, $complect, $section, $styles)
    {

        $totalCount = $this->getInfoblocksCount($complect);
        $fonts = $styles['fonts'];
        $paragraphs = $styles['paragraphs'];
        $tableStyle = $styles['tables'];
        $tableParagraphs = $tableStyle['general']['paragraphs'];
        $tableFonts = $styles['fonts']['table'];

        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];
        $section->addPageBreak();
        $section->addText('Информационное наполнение', $fonts['h1']);
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
                                $section->addText($currentInfoblock['name'], $fonts['text']['normal'], $paragraphs['general'], $paragraphs['align']['left']);
                            } else   if ($descriptionMode === 1) {
                                $section->addText($currentInfoblock['name'], $fonts['text']['bold'], $paragraphs['head'], $paragraphs['align']['center']);
                                $section->addText($currentInfoblock['shortDescription'], $fonts['text']['normal'], $paragraphs['general'], $paragraphs['align']['left']);
                                $section->addTextBreak(1);
                            } else   if ($descriptionMode === 2) {
                                $section->addText($currentInfoblock['name'], $fonts['text']['bold'], $paragraphs['head'], $paragraphs['align']['center']);
                                $section->addText($currentInfoblock['descriptionForSale'], $fonts['text']['normal'], $paragraphs['general'], $paragraphs['align']['left']);
                                $section->addTextBreak(1);
                            } else   if ($descriptionMode === 3) {
                                $section->addText($currentInfoblock['name'], $fonts['text']['bold'], $paragraphs['head'], $paragraphs['align']['center']);
                                $section->addText($currentInfoblock['descriptionForSale'], $fonts['text']['normal'], $paragraphs['general'], $paragraphs['align']['left']);
                                $section->addTextBreak(1);
                            }
                        }
                    }
                }
            }
        } else if ($styleMode == 'table') {




            $fullWidth = $styles['page']['pageSizeW'];
            $marginRight = $styles['page']['marginLeft'];
            $marginLeft = $styles['page']['marginRight'];
            $contentWidth = ($fullWidth - $marginLeft - $marginRight) / 2;
            $innerContentWidth = ($fullWidth - $marginLeft - $marginRight) - 30;
            $paragraphStyle  = [...$paragraphs['general'], ...$paragraphs['align']['left']];
            $paragraphTitleStyle  = [...$paragraphs['head'], ...$paragraphs['align']['center']];
            $textStyle = $fonts['text']['bold'];
            $titleStyle = $fonts['text']['bold'];

            $fancyTableStyleName = 'TableStyle';


            $section->addTableStyle($fancyTableStyleName, $tableStyle['general']['table'], $tableStyle['general']['row']);
            $table = $section->addTable($fancyTableStyleName);
            $table->addRow();
            $cell = $table->addCell($contentWidth, $tableStyle['general']['cell']);

            $innerTable = $cell->addTable($tableStyle['inner']['table']);
            $innerTable->addRow();
            $innerTableCell = $innerTable->addCell($innerContentWidth, $tableStyle['inner']['cell']); // Уменьшаем ширину, чтобы создать отступ




            $count = 0;
            $isTwoColExist = false;
            foreach ($complect as $group) {
                // $table->addCell($contentWidth, $fancyTableCellStyle)->addText($group['groupsName'], $headingStyle);


                foreach ($group['value'] as $infoblock) {

                    if (array_key_exists('code', $infoblock)) {
                        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();

                        if ($currentInfoblock) {

                            if ($count < ($totalCount['infoblocks'] / 2)) {

                                $this->addInfoblockToCell(
                                    $styleMode,
                                    $innerTableCell,
                                    $currentInfoblock,
                                    $descriptionMode,
                                    // $tableParagraphs['center'],
                                    $paragraphStyle,
                                    $paragraphTitleStyle,
                                    $textStyle,
                                    $titleStyle
                                );
                            } else {
                                // Если count нечетный, добавляем вторую ячейку в текущую строку
                                if (!$isTwoColExist) {
                                    $cell = $table->addCell($contentWidth,  $tableStyle['general']['table']);
                                    $innerTable = $cell->addTable($tableStyle['inner']['table']);
                                    $innerTable->addRow();
                                    $innerTableCell = $innerTable->addCell($innerContentWidth, $tableStyle['inner']['cell']); // Уменьшаем ширину, чтобы создать отступ
                                    $isTwoColExist = true;
                                }

                                $this->addInfoblockToCell(
                                    $styleMode,
                                    $innerTableCell,
                                    $currentInfoblock,
                                    $descriptionMode,
                                    // $tableParagraphs['center'],
                                    $paragraphStyle,
                                    $paragraphTitleStyle,
                                    $textStyle,
                                    $titleStyle
                                );
                            }

                            // $section->addTextBreak(1);
                            $count = $count  + 1;
                        }
                    }
                }
            }
        } else if ($styleMode == 'tableWithGroup') {
            $fullWidth = $styles['page']['pageSizeW'];
            $marginRight = $styles['page']['marginLeft'];
            $marginLeft = $styles['page']['marginRight'];
            $contentWidth = ($fullWidth - $marginLeft - $marginRight - 100);
            $innerContentWidth = ($fullWidth - $marginLeft - $marginRight) - 30;
            $paragraphStyle  = [...$paragraphs['general'], ...$paragraphs['align']['left']];
            $paragraphTitleStyle  = [...$paragraphs['head'], ...$paragraphs['align']['center']];
            $textStyle = $fonts['text']['normal'];
            $titleStyle = $fonts['text']['bold'];


            $fancyTableStyleName = 'TableStyle';

            $section->addTableStyle($fancyTableStyleName, $tableStyle['general']['table'], $tableStyle['general']['row']);
            $table = $section->addTable($fancyTableStyleName);



            $count = 0;
            $isTwoColExist = false;
            foreach ($complect as $group) {
                // $table->addCell($contentWidth, $fancyTableCellStyle)->addText($group['groupsName'], $headingStyle);
                $table->addRow();
                $cell = $table->addCell($contentWidth, $tableStyle['general']['cell']);

                $innerTable = $cell->addTable($tableStyle['inner']['table']);
                $innerTable->addRow();
                $innerTableCell = $innerTable->addCell($contentWidth, $tableStyle['inner']['cell']);
                $innerTableCell->addText($group['groupsName'], $fonts['text']['bold'], $paragraphTitleStyle);

                foreach ($group['value'] as $infoblock) {

                    if (array_key_exists('code', $infoblock)) {
                        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();

                        if ($currentInfoblock) {
                            $table->addRow();
                            $cell = $table->addCell($contentWidth, $tableStyle['general']['cell']);

                            $innerTable = $cell->addTable($tableStyle['inner']['table']);
                            $innerTable->addRow();
                            $innerTableCell = $innerTable->addCell($contentWidth, $tableStyle['inner']['cell']); // Уменьшаем ширину, чтобы создать отступ
                            $isTwoColExist = true;


                            $this->addInfoblockToCell(
                                $styleMode,
                                $innerTableCell,
                                $currentInfoblock,
                                $descriptionMode,
                                // $tableParagraphs['center'],
                                $paragraphStyle,
                                $paragraphTitleStyle,
                                $textStyle,
                                $titleStyle
                            );

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
                $fonts['h2'],
                $fonts['text']['normal'],
                $fonts['text']['bold']
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

    protected function addInfoblockToCell(
        $tableType,
        $cell,
        $infoblock,
        $descriptionMode,
        $paragraphStyle,
        $paragraphTitleStyle,
        $textStyle,
        $titleStyle
    ) {

        //todo align depends from table style
        switch ($descriptionMode) {
            case 0:
                $cell->addText($infoblock['name'], $textStyle, $paragraphStyle);
                break;
            case 1:
                $cell->addText($infoblock['name'], $titleStyle, $paragraphStyle);
                $cell->addText($infoblock['shortDescription'], $textStyle, $paragraphStyle);
                if ($tableType == 'table') {
                    $cell->addTextBreak(1);
                }

                break;
            case 2:
            case 3:
                $cell->addText($infoblock['name'], $titleStyle, $paragraphStyle);
                $cell->addText($infoblock['descriptionForSale'], $textStyle, $paragraphStyle);
                if ($tableType == 'table') {
                    $cell->addTextBreak(1);
                }
                break;
        }
    }

    protected function getPriceSection($section, $styles, $price)
    {
        try {
            $section->addPageBreak();


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

            $section->addTextBreak(1);
            $section->addText('Цена за комплект', $styles['fonts']['h1']);

            $fancyTableStyleName = 'DocumentPrice';


            $fullWidth = $styles['page']['pageSizeW'];
            $marginRight = $section->getStyle()->getMarginRight();
            $marginLeft = $section->getStyle()->getMarginLeft();
            $contentWidth = $fullWidth - $marginLeft - $marginRight;


            //TABLE
            $allPrices = $price['cells'];

            //SORT CELLS
            foreach ($allPrices as $target) {
                log::info('target', ['$target' => $target]);
                if (is_array($target) && !empty($target)) {
                    foreach ($target as $product) {
                        log::info('product', ['$product' => $target]);
                        if (is_object($product) && isset($product->cells) && is_array($product->cells) && !empty($product->cells)) {
                            $product->cells = array_filter($product->cells, function ($prc) {
                                return $prc['isActive'];
                            });

                            usort($product->cells, function ($a, $b) {
                                return $a->order - $b->order;
                            });
                        }
                        log::info('iter cells', ['$product' => $product->cells]);
                    }
                    // unset($product); // Очищаем ссылку на $product после завершения внутреннего цикла
                }
            }
            // unset($target); // Очищаем ссылку на $target после завершения внешнего цикла

            log::info('cells sort ', ['$cells' => $allPrices['general'][0]['cells']]);

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

            if ($isTable) {

                // Расчет ширины каждой ячейки в зависимости от количества столбцов
                // if ($allPrices['general']) {
                //     // $activePriceCellsGeneral = array_filter($allPrices['general'], function ($prc) {
                //     //     return $prc['isActive'];
                //     // });

                // }
                // if ($allPrices['alternative']) {
                //     $activePriceCellsAlternative = array_filter($allPrices['alternative'], function ($prc) {
                //         return $prc['isActive'];
                //     });
                // }
                // if ($allPrices['total']) {
                //     // $activePriceCellsAlternative = array_filter($allPrices['total'], function ($prc) {
                //     //     return $prc['isActive'];
                //     // });
                // }

                if ($allPrices['general'][0]) {
                    $numCells = count($allPrices['general'][0]['cells']); // Количество столбцов


                    $fancyTableStyleName = 'TableStyle';

                    $section->addTableStyle(
                        $fancyTableStyleName,
                        $styles['tables']['general']['table'],
                        $styles['tables']['general']['row']
                    );
                    $table = $section->addTable($fancyTableStyleName);

                    $table->addRow();

                    $count = 0;
                    //TABLE HEADER
                    foreach ($allPrices['general'][0]['cells'] as $priceCell) {

                        $this->getPriceCell(true, $table, $styles, $priceCell, $contentWidth, $isHaveLongPrepayment, $numCells);
                        $count += 1;
                    }

                    //TABLE BODY
                    foreach ($allPrices as $target) {
                        if ($target) {
                            if (is_array($target) && !empty($target)) {
                                foreach ($target as $product) {
                                    log::info('product', ['$product' => $product]);
                                    if ($product) {
                                        if (is_object($product) && isset($product->cells) && is_array($product->cells) && !empty($product->cells)) {
                                            $table->addRow();
                                            foreach ($product->cells as $cell) {

                                                $this->getPriceCell(false, $table, $styles, $cell, $contentWidth, $isHaveLongPrepayment, $numCells);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    // foreach ($activePriceCellsGeneral as  $prc) {
                    //     $table->addRow();
                    //     foreach ($prc['cells'] as $cll) {
                    //         $this->getPriceCell(false, $table, $styles, $cll, $contentWidth, $isHaveLongPrepayment, $numCells);
                    //     }
                    // }

                    // if ($priceDataAlternative) {

                    //     foreach ($priceDataAlternative as $prc) {
                    //         $table->addRow();

                    //         foreach ($prc['cells'] as $cll) {

                    //             // if ($cll['isActive']) {
                    //             $this->getPriceCell(false, $table, $styles, $cll, $contentWidth, $isHaveLongPrepayment, $numCells);
                    //             // }
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

    protected function getPriceCell($isHeader, $table, $styles, $priceCell, $contentWidth, $isHaveLongPrepayment, $allCellsCount)
    {
        $code = $priceCell['code'];

        $longWidth = 2700;
        $without = 1;
        if ($isHaveLongPrepayment) {
            $longWidth = 3300;
            $without = 3;
        }
        $cellWidth = ($contentWidth - $longWidth) / ($allCellsCount - $without);
        $outerWidth =  $cellWidth;
        $innerWidth = $outerWidth - 30;



        $tableStyle = $styles['tables'];
        $paragraphs = $tableStyle['general']['paragraphs'];
        $fonts = $styles['fonts']['table'];
        $textTableGroupTitleParagraph = $paragraphs['center'];

        $tableHeaderFont = $fonts['h2'];
        $tableBodyFont = $fonts['text'];

        if ($code) {


            switch ($code) {
                case 'name':  //Наименование
                    $textTableGroupTitleParagraph =  $paragraphs['left'];
                    $outerWidth =  $cellWidth + 2700;
                    $innerWidth = $outerWidth - 30;
                    $tableBodyFont =  $fonts['h2'];
                    break;
                case 'quantity': //Количество
                    if ($priceCell['name'] == 'Количество') {
                        $outerWidth =  $cellWidth - 500;
                        $innerWidth = $outerWidth - 30;
                    } else {
                        $outerWidth =  $cellWidth + 500;
                        $innerWidth = $outerWidth - 30;
                    }

                case 'prepayment':  // При внесении предоплаты от
                    // $outerWidth =  $cellWidth + 500;
                    // $innerWidth = $outerWidth - 30;
                    break;


                case 'discountprecent': //Скидка, %
                    // $outerWidth =  $cellWidth - 500;
                    // $innerWidth = $outerWidth - 30;


                    $outerWidth =  $cellWidth + 1000;
                    $innerWidth = $outerWidth - 30;

                case 'measure': //Единица
                    $outerWidth =  $cellWidth - 500;
                    $innerWidth = $outerWidth - 30;

                case 'measureCode': //Кодовое обозначение единицы
                case 'contract':
                case 'supply':
                case 'supplyOffer':

                case 'discountamount': //Скидка в рублях
                case 'current': //Цена
                case 'currentmonth': //Цена в месяц
                case 'default': //Цена по прайсу
                case 'defaultmonth': //Цена по прайсу в месяц
                case 'prepaymentsum':  // При внесении предоплаты от
            }

            $cellValue = $priceCell['value'];
            $font  = $tableBodyFont;
            if ($isHeader) {
                $cellValue = $priceCell['name'];
                $font  = $tableHeaderFont;
            }


            // $totalWidth =  $totalWidth + $outerWidth;

            $cell = $table->addCell($outerWidth, $tableStyle['general']['cell']);
            $innerTable = $cell->addTable($tableStyle['inner']['table']);
            $innerTable->addRow();
            $innerTableCell = $innerTable->addCell($innerWidth, $tableStyle['inner']['cell'])
                ->addText($cellValue, $font, $textTableGroupTitleParagraph);
        }
        return $table;

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
    }
}
