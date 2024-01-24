<?php

namespace App\Http\Controllers;

use App\Models\Infoblock;
use Ramsey\Uuid\Uuid;
use PhpOffice\PhpWord\Shared\Converter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Mockery\Undefined;
use morphos\Russian\MoneySpeller;

class DocumentController extends Controller
{
    protected $documentStyle;

    public function __construct()
    {

        $colors = [
            'general' => '000000',

            'corporate' =>  '34c3f1',
            'second' =>  '000000',
            'white' =>  'ffffff',
            'shadow' =>  'e5e5e5',
            'atention' =>  'd32c65',
            'shadowText' =>  '343541',

        ];
        $generalFont = [
            'name' => 'Arial',
            'color' => $colors['general'],
            'lang' => 'ru-RU',
        ];
        $corporateFont = [
            'name' => 'Arial',
            'color' => $colors['corporate'],
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

                'general' => [
                    'color' => '000000'
                ],
                // 'corporate' => [
                //     'color' => '0262ae'
                // ],
                'corporate' => [
                    'color' => '34c3f1'
                ],
                'second' => [
                    'color' => '000000'
                ],
                'white' => [
                    'color' => 'ffffff'
                ],
                'shadow' => [
                    'color' => 'e5e5e5'
                ],
                'atention' => [
                    'color' => 'd32c65'
                ],
                'shadowText' => [
                    'color' => '343541'
                ],
                'general' => [
                    'color' => '000000'
                ],



            ],

            'fonts' => [
                'general' => [
                    'name' => 'Arial',
                    'color' => '000000',
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
                    'oficial' => [
                        ...$generalFont,
                        'size' => 11,
                        'spaceAfter' => 1,    // Интервал после абзаца
                        'spaceBefore' => 0,   // Интервал перед абзацем
                        'lineHeight' => 1.5,  // Высота строки

                    ],
                    'corporate' => [
                        ...$corporateFont,
                        'size' => 11,
                        'spaceAfter' => 1,    // Интервал после абзаца
                        'spaceBefore' => 0,   // Интервал перед абзацем
                        'lineHeight' => 1.5,  // Высота строки

                    ]

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
                        'cellMargin' => 20,
                        // 'valign' => 'bottom',
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellMarginTop' => 30,
                        'cellMarginRight' => 30,
                        'cellMarginBottom' => 30,
                        'cellMarginLeft' => 30,
                        // ]

                    ],
                    'table' => [
                        'borderSize' => 0,
                        'borderColor' => 'FFFFFF',
                        'cellMargin' => 70,
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellMarginTop' => 70,
                        'cellMarginRight' => 70,
                        'cellMarginBottom' => 70,
                        'cellMarginLeft' => 70,


                    ],

                ],

                'general' => [
                    'table' => [
                        'borderSize' => 7,
                        'borderColor' => '000000',
                        'cellMargin' => 20,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellSpacing' => 20
                    ],
                    'row' => [
                        'cellMargin' => 20, 'borderSize' => 0, 'bgColor' => '66BBFF', 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
                    ],
                    'cell' => [
                        // 'valign' => 'center',
                        'borderSize' => 6,
                        // 'borderColor' => '000000',  // Цвет границы (чёрный)
                        'cellMarginTop' => 40,
                        'cellMarginRight' => 40,
                        'cellMarginBottom' => 40,
                        'cellMarginLeft' => 40,
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
                ],
                'borderbottom' => [

                    'borderBottomSize' => 7,
                    'borderColor' => '000000',
                    // 'borderColor' => '000000',
                    'cellMargin' => 0,
                    // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                    // 'cellSpacing' => 0


                ],
                'total' => [

                    'cell' => [
                        'valign' => 'center',
                        // 'borderBottomSize' => 6,
                        // 'borderColor' => '000000',  // Цвет границы (чёрный)
                        'cellMarginTop' => 10,
                        'cellMarginRight' => 10,
                        'cellMarginBottom' => 10,
                        'cellMarginLeft' => 10,
                    ],

                ],
                'border' => [
                    'top' => [
                        // 'borderSize' => 7,
                        'borderTopSize' => 7,
                        'borderColor' => '000000',  // Цвет границы (чёрный)
                    ],
                    'bottom' => [
                        'borderBottomSize' => 7,
                        // 'borderBottom' => 7,
                    ],
                    'left' => [
                        'borderLeft' => 7,
                    ],
                    'right' => [
                        'borderRight' => 7,
                    ],
                ],
                'alignment' => [
                    'center' =>
                    [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                    ],
                    'start' =>
                    [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::START,
                    ],

                ],
                'valign' => [
                    'center' =>
                    [
                        'valign' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                    ],
                    'top' =>
                    [
                        'valign' => 'top',
                    ],
                    'bottom' =>
                    [
                        'valign' => 'bottom',
                    ],

                ]
            ],
            'header' => [
                'logo' => [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END,
                    'width' => 120,

                    'wrappingStyle' => 'behind'
                    // 'height' => 'auto',
                ]
            ],
            //'inline', 'behind', 'infront', 'square', 'tight'
            'stamp' => [
                'width'            => 120,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'valign' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'wrappingStyle' => 'behind',
                'marginTop'        => 120,
                'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE,
                'posHorizontal'    => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_CENTER,
                'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_COLUMN,
                'posVertical'      => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_CENTER,
                'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_LINE,

            ],
            'signature' => [
                'width'            => 100,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'valign' => 'center',
                // 'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE,
                // 'posHorizontal'    => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_CENTER,
                // 'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_COLUMN,
                // 'posVertical'      => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_CENTER,
                // 'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_LINE,
                'wrappingStyle' => 'infront',
                // 'marginLeft'       => 120,
                // 'marginTop'        => 120,
            ]


        ];
    }


    public function getDocument($data)

    {
        // try {

        //Data
        $templateType = $data['template']['type'];
        //header-data
        $providerRq = $data['provider']['rq'];


        //infoblocks data
        $infoblocksOptions = [
            'description' => $data['infoblocks']['description']['current'],
            'style' => $data['infoblocks']['style']['current']['code'],
        ];
        $complect = $data['complect'];


        //manager
        $manager = $data['manager'];
        //UF_DEPARTMENT
        //SECOND_NAME


        //fields
        $fields = $data['template']['fields'];


        //letter
        $withLetter = false;
        foreach ($fields as $field) {
            if ($field && $field['code']) {
                if (
                    $field['code'] == 'isLetter' && $field['value'] && $field['value'] !== '0'
                    && $field['value'] !== 'false'
                    && $field['value'] !== 'null'
                    && $field['value'] !== ''
                ) {
                    $withLetter = true;
                }
            }
        }



        // STYLES
        $styles = $this->documentStyle;


        //RESULT
        //result document
        $uid = Uuid::uuid4()->toString();
        $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/' . $data['userId']);


        if (!file_exists($resultPath)) {
            mkdir($resultPath, 0775, true); // Создать каталог с правами доступа
        }

        // Проверить доступность каталога для записи
        if (!is_writable($resultPath)) {
            throw new \Exception("Невозможно записать в каталог: $resultPath");
        }


        $resultFileName = $templateType . '_' . $uid . '.docx';
        $document = new \PhpOffice\PhpWord\PhpWord();


        //create document
        $section = $document->addSection($styles['page']);

        //Header
        $headerSection = $this->getHeader($section, $styles,  $providerRq);




        //Main





        // Переменная для отслеживания, находимся ли мы в выделенном блоке
        $inHighlight = false;


        // $letterSection = $this->getLetter($section, $styles,  $fields);

        // $stampsSection = $this->getStamps($section, $styles,  $providerRq);
        // $infoblocksSection = $this->getInfoblocks($section, $styles, $infoblocksOptions, $complect);

        $priceSection = $this->getPriceSection($section, $styles,  $data['price']);
        $stampsSection = $this->getStamps($section, $styles,  $providerRq);

        $invoice = $this->getInvoice($section, $styles, $data['price'], $providerRq);
        $stampsSection = $this->getStamps($section, $styles,  $providerRq);




        //Footer
        if ($manager && $manager['NAME']) {
            //data

            $this->getFooter($section, $styles, $manager);
        }







        // //СОХРАНЕНИЕ ДОКУМЕТА
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document, 'Word2007');
        $objWriter->save($resultPath . '/' . $resultFileName);

        // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ
        $link = asset('storage/clients/' . $data['domain'] . '/documents/' . $data['userId'] . '/' . $resultFileName);

        return APIController::getSuccess([
            // 'data' => $data,
            'link' => $link,
            // 'pageSizeHLetter' => $pageSizeHLetter,
            // 'pageSizeHStamp' => $pageSizeHStamp,


            // 'testInfoblocks' => $testInfoblocks
        ]);
        // } catch (\Throwable $th) {
        //     return APIController::getError(
        //         'something wrong ' . $th->getMessage(),
        //         [
        //             'data' => $data,
        //             'styleMode' => $infoblocksOptions['style']

        //         ]
        //     );
        // }
    }

    protected function getInfoblocks($section, $styles, $infoblocksOptions, $complect)
    {

        $totalCount = $this->getInfoblocksCount($complect);
        $fonts = $styles['fonts'];
        $paragraphs = $styles['paragraphs'];
        $tableStyle = $styles['tables'];
        $tableParagraphs = $tableStyle['general']['paragraphs'];
        $tableFonts = $styles['fonts']['table'];

        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];

        $section->addTextBreak(1);
        $section->addText('Информационное наполнение', $fonts['h1']);
        // $section->addTextBreak(1);


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
            $textStyle = $fonts['text']['normal'];
            $titleStyle = $fonts['text']['bold'];

            $fancyTableStyleName = 'TableStyle';


            $section->addTableStyle($fancyTableStyleName, $tableStyle['general']['table'], $tableStyle['general']['row']);
            $table = $section->addTable($fancyTableStyleName);
            $table->addRow();
            $cell = $table->addCell(
                $contentWidth,
                $tableStyle['general']['cell']
            );

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
            // $section->addPageBreak();


            //ТАБЛИЦА ЦЕН
            $isHaveLongPrepayment = $this->getIsHaveLongPrepayment($price['cells']);


            // $cells = [];
            $isTable = $price['isTable'];


            $section->addTextBreak(1);
            $section->addText('Цена за комплект', $styles['fonts']['h1']);

            $fancyTableStyleName = 'DocumentPrice';


            $fullWidth = $styles['page']['pageSizeW'];
            $marginRight = $section->getStyle()->getMarginRight();
            $marginLeft = $section->getStyle()->getMarginLeft();
            $contentWidth = $fullWidth - $marginLeft - $marginRight;



            $comePrices = $price['cells'];

            //SORT CELLS
            $sortActivePrices = $this->getSortActivePrices($comePrices);
            log::info('sortActivePrices', ['$sortActivePrices' => $sortActivePrices['general'][0]['cells']]);
            $allPrices =  $sortActivePrices;


            //IS WITH TOTAL 
            $withTotal = $this->getWithTotal($allPrices);

            //TABLE
            if ($isTable) {


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

                        $this->getPriceCell(true, false, $table, $styles, $priceCell, $contentWidth, $isHaveLongPrepayment, $numCells);
                        $count += 1;
                    }

                    //TABLE BODY
                    foreach ([$allPrices['general'], $allPrices['alternative']] as $target) {
                        if ($target) {
                            if (is_array($target) && !empty($target)) {
                                foreach ($target as $product) {

                                    if ($product) {
                                        if (is_array($product) && !empty($product) && is_array($product['cells']) && !empty($product['cells'])) {
                                            $table->addRow();
                                            foreach ($product['cells'] as $cell) {

                                                $this->getPriceCell(false, false, $table, $styles, $cell, $contentWidth, $isHaveLongPrepayment, $numCells);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if ($withTotal) {
                        $this->getTotalPriceRow($allPrices, $table, $styles, $contentWidth, $isHaveLongPrepayment, $numCells);
                        $section->addTextBreak(3);
                        $totalSum = $allPrices['general'][0]['cells'];
                        $textTotalSum = $this->getTotalSum($allPrices, true);
                        $section->addText($textTotalSum, $styles['fonts']['text']['normal'],  $styles['paragraphs']['head'], $styles['paragraphs']['align']['right']);
                    }
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

    protected function getPriceCell(
        $isHeader,
        $isTotal,
        $table,
        $styles,
        $priceCell,
        $contentWidth,
        $isHaveLongPrepayment,
        $allCellsCount,
    ) {
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
        $outerCellStyle = $tableStyle['general']['cell'];
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

            if ($isTotal) {
                $outerCellStyle = $tableStyle['total']['cell'];
                if ($code == 'name') {
                    $cellValue = 'Итого';
                    $font  =  $tableHeaderFont;
                } else if ($code == 'prepaymentsum') {
                    $cellValue = $priceCell['value'];
                    $font  = $tableHeaderFont;
                } else {
                    $cellValue = '';
                }
            }

            // $totalWidth =  $totalWidth + $outerWidth;

            $cell = $table->addCell($outerWidth, $outerCellStyle);
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
                                log::info('getSort : product', ['product' => $product]);
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

        // return $isHaveLongPrepayment;
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
    protected function getTotalSum(
        $allPrices,
        $isString,
    ) {
        $result = 0;
        $result = false;
        $totalCells =  $allPrices['total'][0]['cells'];
        foreach ($totalCells as $cell) {
            if ($cell['code'] === 'prepaymentsum') {
                $result = $cell['value'];
            }
        }
        if ($isString) {
            $result = MoneySpeller::spell($result, MoneySpeller::RUBLE);
            $firstChar = mb_strtoupper(mb_substr($result, 0, 1, "UTF-8"), "UTF-8");
            $restOfText = mb_substr($result, 1, mb_strlen($result, "UTF-8"), "UTF-8");
            $text = $firstChar . $restOfText . ' без НДС';
            $result = $text;
        }

        return $result;
    }

    protected function getHeader($section, $styles, $providerRq)
    {
        //HEADER
        $header = $section->addHeader();


        //data for header

        //     'fullname' => $providerRq['fullname'],
        //     'primaryAdresss' => $providerRq['primaryAdresss'],
        //     'inn' => $providerRq['inn'],
        //     'kpp' => $providerRq['kpp'],
        //     'email' => $providerRq['email'],
        //     'phone' => $providerRq['phone'],


        $first = $providerRq['fullname'];
        if ($providerRq['inn']) {
            $first = $first . ', ИНН: ' . $providerRq['inn'] . ', ';
        }
        if ($providerRq['kpp']) {
            $first = $first . ', КПП: ' . $providerRq['kpp'] . ', ';
        }
        $second = $providerRq['primaryAdresss'];
        if ($providerRq['phone']) {
            $second = $second . ', ' . $providerRq['phone'];
        }
        if ($providerRq['email']) {
            $second = $second . ', ' . $providerRq['email'];
        }




        // create header

        $tableHeader = $header->addTable();
        $tableHeader->addRow();
        $headerRqWidth = $styles['page']['pageSizeW'] / 2.5;
        $headerLogoWidth = $styles['page']['pageSizeW'] / 1.5;

        $headerTextStyle = $styles['fonts']['text']['small'];
        $headerRqParagraf = $styles['paragraphs']['general'];
        $cell = $tableHeader->addCell($headerRqWidth);
        $rqTable = $cell->addTable();

        if ($first) {
            $rqTable->addRow();
            $rqCell = $rqTable->addCell($headerRqWidth);
            $rqCell->addText($first, $headerTextStyle, $headerRqParagraf);
        }
        if ($second) {
            $rqTable->addRow();
            $rqCell = $rqTable->addCell($headerRqWidth);
            $rqCell->addText($second, $headerTextStyle, $headerRqParagraf);
        }







        $logo =  null;
        if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos'])) {
            $logo =  $providerRq['logos'][0];
        }
        if ($logo) {

            $fullPath = storage_path('app/' . $logo['path']);
            if (file_exists($fullPath)) {
                // Добавление изображения в документ PHPWord
                $tableHeader->addCell($headerLogoWidth)->addImage(
                    $fullPath,
                    $styles['header']['logo']
                );
            }
        }

        return $section;
    }
    protected function getFooter($section, $styles, $manager)
    {
        //FOOTER
        //data

        $managerPosition = $manager['WORK_POSITION'];
        if (!$managerPosition) {
            $managerPosition = 'Ваш персональный менеджер';
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


        //styles
        $footerManagerWidth = $styles['page']['pageSizeW'] / 2.5;
        $footerTextStyle = $styles['fonts']['text']['small'];
        $footerManagerParagraf = $styles['paragraphs']['general'];
        $footerManagerParagrafAlign = $styles['paragraphs']['align']['left'];


        //create
        $footer = $section->addFooter();
        $tableFooter = $footer->addTable();
        $tableFooter->addRow();

        $footerManagerNameCell = $tableFooter->addCell($footerManagerWidth);
        $footerManagerNameCell->addText($managerPosition, $footerTextStyle, $footerManagerParagraf, $footerManagerParagrafAlign);
        $footerManagerNameCell->addText($name, $footerTextStyle, $footerManagerParagraf, $footerManagerParagrafAlign);
        $footerManagerNameCell->addText($email, $footerTextStyle, $footerManagerParagraf, $footerManagerParagrafAlign);
        $footerManagerNameCell->addText($phone, $footerTextStyle, $footerManagerParagraf, $footerManagerParagrafAlign);
        return $section;
    }
    protected function getStamps($section, $styles, $providerRq)
    {

        $stamps = $providerRq['stamps'];
        $signatures = $providerRq['signatures'];
        $stamp = null;
        $signature = null;


        if (!empty($stamps)) {
            $stamp = $stamps[0];
        }
        if (!empty($signatures)) {
            $signature = $signatures[0];
        }
        $section->addTextBreak(2);
        $stampsSection = $section->addTable();
        $stampsSection->addRow(
            900,
            $styles['tables']['general']['row'],
        );
        $stampsWidth = $styles['page']['pageSizeW'];

        $cell = $stampsSection->addCell(
            $stampsWidth / 3,
            $styles['tables']['inner']['cell'],
        );

        $stampsFirstString = $providerRq['position'] . ' ' . $providerRq['fullname'];
        if ($providerRq['type'] == 'ip') {
            $stampsFirstString = $providerRq['fullname'];
        }
        $cell->addText(
            $stampsFirstString,
            $styles['fonts']['text']['normal'],
            $styles['paragraphs']['head'],
            $styles['paragraphs']['align']['left']

        );


        $cell = $stampsSection->addCell(
            $stampsWidth / 3,
            $styles['tables']['inner']['cell'],
        );
        if ($stamp) {
            $stampPath = storage_path('app/' . $stamp['path']);
            if (file_exists($stampPath)) {
                // Добавление изображения в документ PHPWord
                $cell->addImage(
                    $stampPath,
                    $styles['stamp']
                );
            }
        }
        if ($signature) {
            $signaturePath = storage_path('app/' . $signature['path']);
            if (file_exists($signaturePath)) {
                // Добавление изображения в документ PHPWord
                $cell->addImage(
                    $signaturePath,
                    $styles['signature']
                );
            }
        }
        $cell = $stampsSection->addCell(
            $stampsWidth / 3,
            $styles['tables']['inner']['cell'],
        );

        $stampsSecondString = '';
        if ($providerRq['type'] == 'org') {
            $stampsSecondString = $providerRq['director'];
        }

        $cell->addText(
            $stampsSecondString,
            $styles['fonts']['text']['normal'],
            $styles['paragraphs']['head'],
            $styles['paragraphs']['align']['right']
        );
        // $section->addPageBreak();
        return $section;
    }

    protected function getLetter($section, $styles, $fields)
    {
        //FOOTER
        //data
        // Стили для обычного и выделенного текста
        $section->addTextBreak(1);
        $letterTextStyle = $styles['fonts']['text']['oficial'];
        $corporateletterTextStyle = $styles['fonts']['text']['corporate'];
        $fullWidth = $styles['page']['pageSizeW'];
        $marginRight = $section->getStyle()->getMarginRight();
        $marginLeft = $section->getStyle()->getMarginLeft();
        $contentWidth = $fullWidth - $marginLeft - $marginRight;
        $leftAlign = $styles['paragraphs']['align']['left'];
        $rightAlign = $styles['paragraphs']['align']['right'];
        $table = $section->addTable();
        $table->addRow();
        $cellWidth = $contentWidth / 2;
        $letterNumberell = $table->addCell($contentWidth);
        $letterNumberell->addText('Номер Письма', $letterTextStyle, $leftAlign);
        $letterRecipientell = $table->addCell($contentWidth);
        $letterRecipientell->addText('Кому', $letterTextStyle, $rightAlign);

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
        $parts = preg_split('/<color>|<\/color>/', $letterText);

        $textRun = $section->addTextRun();

        $inHighlight = false;
        foreach ($parts as $part) {
            // Разбиваем часть на подстроки по символам переноса строки
            $subparts = preg_split("/\r\n|\n|\r/", $part);
            foreach ($subparts as $subpart) {
                if ($inHighlight) {
                    // Добавление выделенного текста
                    $textRun->addText($subpart, $corporateletterTextStyle);
                } else {
                    // Добавление обычного текста
                    $textRun->addText($subpart, $letterTextStyle);
                }
                // Добавление разрыва строки после каждой подстроки, кроме последней
                if ($subpart !== end($subparts)) {
                    $textRun->addTextBreak(1);
                }
            }
            $inHighlight = !$inHighlight;
        }



        return $section;
    }
    protected function getInvoice($section, $styles, $price, $providerRq)
    {
        try {
            //STYLES
            $fonts = $styles['fonts'];
            $paragraphs = $styles['paragraphs'];
            $tableStyle = $styles['tables'];


            //SIZES
            //ТАБЛИЦА ЦЕН

            // $cells = [];
            // $isTable = $price['isTable'];


            $fullWidth = $styles['page']['pageSizeW'];
            $marginRight = $styles['page']['marginLeft'];
            $marginLeft = $styles['page']['marginRight'];
            $contentWidth = ($fullWidth - $marginLeft - $marginRight);
            $innerContentWidth = $contentWidth - 30;
            $paragraphStyle  = [...$paragraphs['general'], ...$paragraphs['align']['left']];
            $paragraphTitleStyle  = [...$paragraphs['head'], ...$paragraphs['align']['center']];
            $textStyle = $fonts['text']['normal'];
            $titleStyle = $fonts['text']['bold'];
            $invoiceHeaderCellWidthFirst = $fullWidth  * 0.48;
            $invoiceHeaderCellWidthFirstInner = ($invoiceHeaderCellWidthFirst - 130);
            $invoiceHeaderCellWidthSecond = $fullWidth  * 0.07;
            $invoiceHeaderCellWidthSecondInner = $invoiceHeaderCellWidthSecond - 30;
            $invoiceHeaderCellWidthThird = $fullWidth  * 0.5;
            $invoiceHeaderCellWidthThirdInner = $invoiceHeaderCellWidthThird - 30;


            $topTableHeight = 2000;
            //SORT CELLS
            $comePrices = $price['cells'];
            $sortActivePrices = $this->getSortActivePrices($comePrices);
            $allPrices =  $sortActivePrices;


            //TABLE


            $fancyTableStyleName = 'InvoiceHeaderTableStyle';

            $section->addTableStyle(
                $fancyTableStyleName,
                $styles['tables']['general']['table'],
                // $styles['tables']['general']['row'],
                // $styles['tables']['general']['cell']
            );


            //INVOICE TOP TABLE
            $table = $section->addTable($fancyTableStyleName);
            $table->addRow($topTableHeight / 2.2);

            $cell = $table->addCell(
                $invoiceHeaderCellWidthFirst,
                $styles['tables']['general']['cell'],
                // $styles['tables']['alignment']['start'],
                // $styles['tables']['valign']['top']

            );


            $innerTable = $cell->addTable(
                $tableStyle['inner']['table'],
                // $styles['tables']['alignment']['start'],
                // $styles['tables']['valign']['bottom']

            );


            $innerTable->addRow($topTableHeight / 2.2 / 2);
            $innerCell1 = $innerTable->addCell(
                $invoiceHeaderCellWidthFirstInner,
                [
                    ...$styles['tables']['inner']['cell'],
                    ...$styles['tables']['alignment']['start'],
                    ...$styles['tables']['valign']['top']
                ]
            );
            $innerCell1->addText("Южный филиал АО 'Райффайзенбанк' г.Краснодар", $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow(
                $topTableHeight / 2.2 / 2
            );
            $innerCell2 = $innerTable->addCell(
                null,

                $styles['tables']['valign']['bottom']

            );
            $innerCell2->addText("Банк получателя", $fonts['text']['small'], $paragraphStyle);



            $cellSecond = $table->addCell(
                $invoiceHeaderCellWidthSecond,
                $styles['tables']['borderbottom']

            );
            $innerTable = $cellSecond->addTable(
                $styles['tables']['borderbottom']


            );
            $innerTable->addRow($topTableHeight / 8);
            $innerCell1 = $innerTable->addCell(
                $invoiceHeaderCellWidthSecond,
                [
                    // ...$styles['tables']['general']['cell'],
                    ...$styles['tables']['borderbottom']
                ]

            );
            $innerCell1->addText("БИК", $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow();
            $innerCell2 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell2->addText("Cx #", $fonts['text']['small'], $paragraphStyle);


            $cellThird = $table->addCell($invoiceHeaderCellWidthThird, $styles['tables']['general']['table']);
            $innerTable = $cellThird->addTable();
            $innerTable->addRow($topTableHeight / 4);
            $innerCell1 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell1->addText("040349556", $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow();
            $innerCell2 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell2->addText("30101810900000000556", $fonts['text']['small'], $paragraphStyle);




            $table->addRow($topTableHeight / 1.6);
            // $table->addCell($invoiceHeaderCellWidthFirst,  $styles['tables']['general']['table']);
            $cell = $table->addCell($invoiceHeaderCellWidthFirst, $styles['tables']['general']['table']);
            $innerTable = $cell->addTable();

            $innerTable->addRow();
            $innerCell1 = $innerTable->addCell();
            $innerCell1->addText("Южный филиал АО 'Райффайзенбанк' г.Краснодар", $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow();
            $innerCell2 = $innerTable->addCell($invoiceHeaderCellWidthFirst);
            $innerCell2->addText("Банк получателя", $fonts['text']['small'], $paragraphStyle);



            $cellSecond = $table->addCell($invoiceHeaderCellWidthSecond, $styles['tables']['general']['table']);
            $innerTable = $cellSecond->addTable();
            $innerTable->addRow($topTableHeight / 4);
            $innerCell1 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell1->addText("БИК", $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow();
            $innerCell2 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell2->addText("Cx #", $fonts['text']['small'], $paragraphStyle);


            $cellThird = $table->addCell($invoiceHeaderCellWidthThird, $styles['tables']['general']['table']);
            $innerTable = $cellThird->addTable();
            $innerTable->addRow($topTableHeight / 4);
            $innerCell1 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell1->addText("040349556", $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow();
            $innerCell2 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell2->addText("30101810900000000556", $fonts['text']['small'], $paragraphStyle);

            // $cellFirst = $table->addCell($invoiceHeaderCellWidthFirst, $tableStyle['general']['table']);
            // $innerTableFirst  = $cellFirst->addTable($tableStyle['inner']['table']);
            // $innerTableFirst->addRow($topTableHeight / 2);
            // $innerTableCellFirst  = $innerTableFirst->addCell($invoiceHeaderCellWidthFirstInner, $tableStyle['general']['cell'], $tableStyle['border']['bottom']);
            // $innerTableCellFirst->addText("Южный филиал АО 'Райффайзенбанк' г.Краснодар", $fonts['text']['small'], $paragraphStyle);
            // $innerTableFirst->addRow();
            // $innerTableCellFirst  = $innerTableFirst->addCell($invoiceHeaderCellWidthFirstInner, $tableStyle['general']['cell']);
            // $innerTableCellFirst->addText("ИНН КПП", $fonts['text']['small'], $paragraphStyle);

            // $innerTableCellFirst->addText("Южный филиал АО 'Райффайзенбанк' г.Краснодар", $fonts['text']['small'], $paragraphStyle);

















            // $cellSecond = $table->addCell($invoiceHeaderCellWidthSecond, $tableStyle['general']['table']);
            // $innerTable = $cellSecond->addTable($tableStyle['inner']['table']);
            // $innerTable->addRow();
            // $innerTableCell = $innerTable->addCell($invoiceHeaderCellWidthSecondInner, $tableStyle['inner']['cell']);
            // $innerTableCell->addText("БИК", $fonts['text']['small'], $paragraphStyle);


            // $cellThird = $table->addCell($invoiceHeaderCellWidthThird, $tableStyle['general']['table']);
            // $innerTableThird = $cellThird->addTable($tableStyle['inner']['table']);
            // $innerTableThird->addRow();
            // $innerTableCellThird = $innerTableThird->addCell($invoiceHeaderCellWidthThirdInner, $tableStyle['inner']['cell']);
            // $innerTableCellThird->addText("Южный филиал АО 'Райффайзенбанк' г.Краснодар", $fonts['text']['small'], $paragraphStyle);





            //TABLE BODY
            $section->addTextBreak(2);
            $section->addText('Счет на оплату', $styles['fonts']['h3'],  $styles['paragraphs']['head'], $styles['paragraphs']['align']['center']);
            // $priceSection = $this->getPriceSection($section, $styles, $price);


            return $section;
        } catch (\Throwable $th) {
            return [
                'resultCode' => 1,
                'result' => null,
                'message' => $th->getMessage()
            ];
        }
    }
}
