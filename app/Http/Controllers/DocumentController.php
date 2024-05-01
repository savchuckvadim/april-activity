<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use App\Models\Infoblock;
use Ramsey\Uuid\Uuid;
use PhpOffice\PhpWord\Shared\Converter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery\Undefined;
use morphos\Gender;
use morphos\Russian\MoneySpeller;

use function morphos\Russian\detectGender;

class DocumentController extends Controller
{
    protected $documentStyle;

    public function __construct()
    {

        $colors = [
            'general' => '120D21',
            'corporate' =>  '005fa8',
            'oficial' => '1A1138',
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
        $oficialFont = [
            'name' => 'Arial',
            'color' => $colors['oficial'],
            'lang' => 'ru-RU',
        ];

        $baseCellMargin = 30;
        $baseCellMarginSmall = 10;

        $baseBorderSize = 4;


        $this->documentStyle = [
            'page' => [
                // 'pageSizeW' => Converter::inchToTwip(8.5), // ширина страницы
                // 'pageSizeH' => Converter::inchToTwip(11),   // высота страницы
                // 'marginLeft' => Converter::inchToTwip(0.5),
                // 'marginRight' => Converter::inchToTwip(0.5),

                'pageSizeW' => Converter::inchToTwip(210 / 25.4), // ширина страницы A4 в twips
                'pageSizeH' => Converter::inchToTwip(297 / 25.4), // высота страницы A4 в twips
                'marginLeft' => Converter::inchToTwip(0.5),       // левый отступ
                'marginRight' => Converter::inchToTwip(0.5),      // правый отступ
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
                        'size' => 8,
                        'lineHeight' => 1,
                        'spaceAfter' => 0,
                        'spaceBefore' => 0,
                    ],
                    'normal' => [
                        ...$generalFont,
                        'size' => 9
                    ],
                    'bold' => [
                        ...$generalFont,
                        'bold' => true,
                        'size' => 10
                    ],
                    'spanBold' => [
                        ...$generalFont,
                        'bold' => true,
                        'size' => 10,
                        'spaceAfter' => 1,    // Интервал после абзаца
                        'spaceBefore' => 0,   // Интервал перед абзацем
                        'lineHeight' => 1.5,  // Высота строки
                    ],
                    'span' => [
                        ...$generalFont,
                        'bold' => false,
                        'size' => 10,
                        'spaceAfter' => 1,    // Интервал после абзаца
                        'spaceBefore' => 0,   // Интервал перед абзацем
                        'lineHeight' => 1.5,  // Высота строки
                    ],
                    'oficial' => [
                        ...$oficialFont,
                        'size' => 10,
                        'spaceAfter' => 1,    // Интервал после абзаца
                        'spaceBefore' => 0,   // Интервал перед абзацем
                        'lineHeight' => 1.5,  // Высота строки

                    ],
                    'corporate' => [
                        ...$corporateFont,
                        'size' => 9,
                        'bold' => true,
                        // 'spaceAfter' => 1,    // Интервал после абзаца
                        // 'spaceBefore' => 0,   // Интервал перед абзацем
                        // 'lineHeight' => 1.5,  // Высота строки

                    ],
                    'big' => [
                        ...$generalFont,
                        'bold' => false,
                        'size' => 12,
                        'spaceAfter' => 1,    // Интервал после абзаца
                        'spaceBefore' => 0,   // Интервал перед абзацем
                        'lineHeight' => 1.5,  // Высота строки
                    ],
                    'bigBold' => [
                        ...$generalFont,
                        'bold' => true,
                        'size' => 12,
                        'spaceAfter' => 1,    // Интервал после абзаца
                        'spaceBefore' => 0,   // Интервал перед абзацем
                        'lineHeight' => 1.5,  // Высота строки
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
                    'spaceBefore' => 100,   // Интервал перед абзацем
                    'lineHeight' => 1,  // Высота строки
                ],
                'tableHead' => [
                    'valign' => 'center',
                    'spaceAfter' => 1,    // Интервал после абзаца
                    'spaceBefore' => 1,   // Интервал перед абзацем
                    'lineHeight' => 1,  // Высота строки
                ],
                'small' => [
                    'valign' => 'center',
                    'spaceAfter' => 0,    // Интервал после абзаца
                    'spaceBefore' => 0,   // Интервал перед абзацем
                    'lineHeight' => 1,  // Высота строки
                ],
                'align' => [
                    'left' => [
                        'alignment' => 'left',

                    ],
                    'right' => [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END,

                    ],
                    'center' => [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,

                    ],
                    'both' => [
                        'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH,

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
                        'cellMargin' => $baseCellMargin,
                        // 'valign' => 'bottom',
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellMarginTop' => $baseCellMargin,
                        'cellMarginRight' => $baseCellMargin,
                        'cellMarginBottom' => $baseCellMargin,
                        'cellMarginLeft' => $baseCellMargin,
                        // ]

                    ],
                    'table' => [
                        'borderSize' => 0,
                        'borderColor' => 'FFFFFF',
                        'cellMargin' => $baseCellMargin,
                        'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        // 'cellMarginTop' => 30,
                        // 'cellMarginRight' => 30,
                        // 'cellMarginBottom' => 30,
                        // 'cellMarginLeft' => 30,


                    ],

                ],

                'general' => [
                    'table' => [
                        'borderSize' => $baseBorderSize,
                        'borderColor' => '000000',
                        // 'cellMargin' =>  $baseCellMarginSmall,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        // 'cellSpacing' => 20
                    ],
                    'row' => [
                        'cellMargin' =>  $baseCellMarginSmall, 'borderSize' => 0, 'bgColor' => '66BBFF', 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
                    ],
                    'cell' => [
                        // 'valign' => 'center',
                        'borderSize' => $baseBorderSize,
                        // 'borderColor' => '000000',  // Цвет границы (чёрный)
                        'cellMarginTop' => $baseCellMargin,
                        'cellMarginRight' => $baseCellMargin,
                        'cellMarginBottom' => $baseCellMargin,
                        'cellMarginLeft' => $baseCellMargin,
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
                'invoice' => [
                    'table' => [

                        'borderSize' => $baseBorderSize,
                        'borderColor' => '000000',
                        'cellMargin' => 0,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellSpacing' => 0,
                        'cellMarginTop' => 0,
                        'cellMarginRight' => 0,
                        'cellMarginBottom' => 0,
                        'cellMarginLeft' => 0,
                        'cellSpacing' => 0


                    ],
                    'empty' => [

                        'borderSize' => 0,
                        'borderColor' => 'FFFFFF',
                        'cellMargin' => 0,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellSpacing' => 0,
                        // 'cellMarginTop' => 30,
                        // 'cellMarginRight' => 30,
                        // 'cellMarginBottom' => 30,
                        // 'cellMarginLeft' => 30,
                        // 'cellSpacing' => 30


                    ],
                    'innertable' => [

                        'borderSize' => 0,
                        'borderColor' => 'FFFFFF',
                        'cellMargin' => 0,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                        'cellSpacing' => 0,
                        'cellMargin' => $baseCellMargin,
                        // 'cellMarginRight' => 40,
                        // 'cellMarginBottom' => 40,
                        // 'cellMarginLeft' => 40,


                    ],
                    'cell' =>  [

                        // 'borderBottomSize' => 7,
                        // 'borderColor' => '000000',
                        // 'cellMargin' => 30,
                        'cellMargin' => $baseCellMargin,
                        'cellSpacing' => 0,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,



                    ],

                    'inn' =>  [

                        'borderRightSize' => $baseBorderSize,
                        'borderColor' => '000000',
                        'cellMargin' => 30,
                        'cellSpacing' => 0,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,



                    ],
                    'topleft' =>  [

                        'borderTopSize' => $baseBorderSize,
                        'borderLeftSize' => $baseBorderSize,
                        'borderColor' => '000000',
                        'cellMargin' => $baseCellMargin,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,



                    ],
                    'top' =>  [

                        'borderTopSize' => $baseBorderSize,

                        'borderColor' => '000000',
                        'cellMargin' => $baseCellMargin,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,



                    ],
                    'bottom' =>  [

                        'borderBottomSize' => $baseBorderSize,

                        'borderColor' => '000000',
                        'cellMargin' => $baseCellMargin,
                        // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,



                    ],
                ],

                'total' => [

                    'cell' => [
                        'valign' => 'center',
                        // 'borderBottomSize' => 6,
                        // 'borderColor' => '000000',  // Цвет границы (чёрный)
                        'cellMarginTop' =>  $baseCellMarginSmall,
                        'cellMarginRight' =>  $baseCellMarginSmall,
                        'cellMarginBottom' =>  $baseCellMarginSmall,
                        'cellMarginLeft' =>  $baseCellMarginSmall,
                    ],

                ],
                'border' => [
                    'top' => [
                        // 'borderSize' => 7,
                        'borderTopSize' => $baseBorderSize,
                        'borderColor' => '000000',  // Цвет границы (чёрный)
                    ],
                    'bottom' => [
                        'borderBottomSize' => $baseBorderSize,
                        // 'invoice' => 7,
                    ],
                    'left' => [
                        'borderLeft' => $baseBorderSize,
                    ],
                    'right' => [
                        'borderRight' => $baseBorderSize,
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
                    // 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END,
                    'width' => 120,

                    'wrappingStyle' => 'behind'
                    // 'height' => 'auto',
                ],

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
                // 'posVertical'      => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_CENTER,
                'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_LINE,

            ],
            'signature' => [
                'width'            => 100,
                'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER,
                'valign' => 'center',
                // 'positioning' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE,
                'posHorizontal'    => \PhpOffice\PhpWord\Style\Image::POSITION_HORIZONTAL_CENTER,
                // 'posHorizontalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_COLUMN,
                'posVertical'      => \PhpOffice\PhpWord\Style\Image::POSITION_VERTICAL_CENTER,
                // 'posVerticalRel' => \PhpOffice\PhpWord\Style\Image::POSITION_RELATIVE_TO_LINE,
                'wrappingStyle' => 'infront',
                // 'marginLeft'       => 120,
                // 'marginTop'        => 120,
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
                'end' =>
                [
                    'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END,
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


        ];
    }


    public function getDocument($data)
    {
        try {

            if ($data &&  isset($data['template'])) {
                $template = $data['template'];
                if ($template && isset($template['id'])) {


                    $templateId = $template['id'];
                    $domain = $data['template']['portal'];
                    $dealId = $data['dealId'];



                    //get counter test 
                    // $counter = Counter::whereHas('templates', function ($query) use ($templateId) {
                    //     $query->where('templates.id', $templateId);
                    // })->first();

                    $providerRq = $data['provider']['rq'];
                    //document number
                    $documentNumber = CounterController::getCount($providerRq['id'], 'offer');

                    // /invoice base number
                    // $step1 = preg_replace_callback(
                    //     '/([а-яА-Яa-zA-Z])\W+/u',
                    //     function ($matches) {
                    //         return $matches[1]; // Возвращает найденную букву без следующих за ней символов
                    //     },
                    //     $documentNumber
                    // );

                    // Затем удаляем нежелательные символы в конце строки, если они не являются цифрами
                    $invoiceBaseNumber =  preg_replace('/\D/', '', $documentNumber);


                    //Data
                    $templateType = $data['template']['type'];


                    //header-data

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
                    $sortActivePrices = $this->getSortActivePrices($comePrices);
                    $allPrices =  $sortActivePrices;
                    $general = $allPrices['general'];
                    $alternative = $allPrices['alternative'];


                    //manager
                    $manager = $data['manager'];
                    //UF_DEPARTMENT
                    //SECOND_NAME


                    //fields
                    $fields = $data['template']['fields'];
                    $recipient = $data['recipient'];


                    //letter
                    $withLetter = false;


                    foreach ($fields as $field) {
                        if ($field && isset($field['code']) && $field['code']) {
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

                    //stamps
                    $withStamps = false;
                    // if (count($providerRq['stamps'])) {
                    //     foreach ($providerRq['stamps'] as $stamp) {
                    //         if (isset($stamp['path'])) {
                    //             if ($stamp['path']) {
                    //                 $withStamps = true;
                    //             }
                    //         }
                    //     }
                    // }

                    // STYLES
                    $styles = $this->documentStyle;






                    $document = new \PhpOffice\PhpWord\PhpWord();


                    //create document
                    $section = $document->addSection($styles['page']);

                    //Header
                    $target = 'ganeral'; //or alternative
                    $headerSection = $this->getHeader($section, $styles,  $providerRq, $isTwoLogo);

                    if ($isTwoLogo) {

                        $this->getDoubleHeader($section, $styles,  $providerRq);
                    }



                    //Main
                    // Переменная для отслеживания, находимся ли мы в выделенном блоке
                    $inHighlight = false;

                    // if ($withLetter) {
                    $letterSection = $this->getLetter($section, $styles, $documentNumber, $fields, $recipient);
                    // if ($withStamps) {
                    //     $stampsSection = $this->getStamps($section, $styles,  $providerRq);
                    // }
                    $priceSection = $this->getPriceSection($section, $styles,  $data['price']);
                    $section->addPageBreak();
                    // }

                    $infoblocksSection = $this->getInfoblocks($section, $styles, $infoblocksOptions, $complect);
                    if ($withStamps) {
                        $section->addTextBreak(1);
                        $stampsSection = $this->getStamps($section, $styles,  $providerRq);
                    }
                    $section->addPageBreak();


                    // $priceSection = $this->getPriceSection($section, $styles,  $data['price']);
                    // if ($withStamps) {
                    //     $section->addTextBreak(2);
                    //     $stampsSection = $this->getStamps($section, $styles,  $providerRq);
                    // }
                    // $section->addPageBreak();



                    // //invoices
                    $invoice = $this->getInvoice($section, $styles, $general, $providerRq, $recipient, $target, $invoiceBaseNumber);
                    if ($withStamps) {
                        $section->addTextBreak(1);
                        $stampsSection = $this->getStamps($section, $styles,  $providerRq);
                    }

                    if (isset($alternative)) {

                        foreach ($alternative as $alternativeCell) {
                            $target = 'alternative';
                            $section->addPageBreak();
                            $invoice = $this->getInvoice($section, $styles, [$alternativeCell], $providerRq, $recipient, $target, $invoiceBaseNumber);
                            if ($withStamps) {
                                $stampsSection = $this->getStamps($section, $styles,  $providerRq);
                            }
                        }
                    }




                    // $stampsSection = $this->getStamps($section, $styles,  $providerRq);




                    // //Footer
                    // if ($manager && $manager['NAME']) {
                    //     //data

                    //     $this->getFooter($section, $styles, $manager);
                    // }







                    // // //СОХРАНЕНИЕ ДОКУМЕТА
                    $uid = Uuid::uuid4()->toString();
                    $shortUid = substr($uid, 0, 4); // Получение первых 4 символов

                    $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/' . $data['userId']);


                    if (!file_exists($resultPath)) {
                        mkdir($resultPath, 0775, true); // Создать каталог с правами доступа
                    }

                    // // Проверить доступность каталога для записи
                    if (!is_writable($resultPath)) {
                        throw new \Exception("Невозможно записать в каталог: $resultPath");
                    }
                    $resultFileName = $documentNumber . '_' . $shortUid . '.docx';
                    $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document, 'Word2007');

                    $objWriter->save($resultPath . '/' . $resultFileName);

                    // // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ

                    $link = asset('storage/clients/' . $domain . '/documents/' . $data['userId'] . '/' . $resultFileName);

                    return APIController::getSuccess([
                        'price' => $price,
                        'link' => $link,
                        'documentNumber' => $documentNumber,
                        // 'counter' => $counter,

                    ]);

                    // $this->setTimeline($domain, $dealId, $link, $documentNumber);
                    // $bitrixController = new BitrixController();
                    // $response = $bitrixController->changeDealStage($domain, $dealId, "PREPARATION");
                    // return APIController::getSuccess([
                    //     'price' => $price,
                    //     'link' => $link,
                    //     'documentNumber' => $documentNumber,
                    //     // 'counter' => $counter,

                    // ]);
                }
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                'something wrong ' . $th->getMessage(),
                [
                    'data' => $data,


                ]
            );
        }
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
        $section->addText('Информационное наполнение', $fonts['h2']);
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
                                if ($currentInfoblock['shortDescription']) {
                                    $section->addText($currentInfoblock['name'], $fonts['text']['corporate'], $paragraphs['head'], $paragraphs['align']['center']);
                                    $section->addText($currentInfoblock['shortDescription'], $fonts['text']['normal'], $paragraphs['general'], $paragraphs['align']['left']);
                                    // $section->addTextBreak(1);
                                }
                            } else {
                                if ($currentInfoblock['descriptionForSale']) {
                                    $section->addText($currentInfoblock['name'], $fonts['text']['bold'], $paragraphs['head'], $paragraphs['align']['center']);
                                    $section->addText($currentInfoblock['descriptionForSale'], $fonts['text']['normal'], $paragraphs['general'], $paragraphs['align']['left']);
                                    $section->addTextBreak(1);
                                }
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
            $paragraphStyle  = [...$paragraphs['general'], ...$styles['alignment']['start']];
            $paragraphTitleStyle  = [...$paragraphs['tableHead'], ...$styles['alignment']['center']];
            $textStyle = $fonts['text']['normal'];
            $titleStyle = $fonts['text']['bold'];


            $fancyTableStyleName = 'TableStyle';

            $section->addTableStyle($fancyTableStyleName, $tableStyle['general']['table'], $tableStyle['general']['row']);
            $table = $section->addTable($fancyTableStyleName);



            $count = 0;
            $isTwoColExist = false;
            foreach ($complect as $group) {
                // $table->addCell($contentWidth, $fancyTableCellStyle)->addText($group['groupsName'], $headingStyle);
                $isBlockHaveInfoblockWithDescription = $this->getIsHaveDescription($group['value']);

                if ($group['groupsName'] !== "Пакет Энциклопедий решений" && $isBlockHaveInfoblockWithDescription) {
                    $table->addRow();
                    $cell = $table->addCell($contentWidth, $tableStyle['general']['cell']);

                    $innerTable = $cell->addTable($tableStyle['inner']['table'], ['valign' => 'bottom']);
                    $innerTable->addRow();
                    $innerTableCell = $innerTable->addCell($contentWidth, $tableStyle['inner']['table']);
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

    protected function getIsHaveDescription($groupBlocks)
    {
        $isBlockHaveInfoblockWithDescription = false;

        foreach ($groupBlocks as $infblck) {
            if (isset($infblck['code'])) {
                $currentInfoblock = Infoblock::where('code', $infblck['code'])->first();

                if ($currentInfoblock) {
                    if (isset($currentInfoblock['description']) && isset($currentInfoblock['descriptionForSale']) && isset($currentInfoblock['shortDescription'])) {
                        if ($currentInfoblock['description'] || $currentInfoblock['descriptionForSale'] || $currentInfoblock['shortDescription']) {
                            $isBlockHaveInfoblockWithDescription = true;
                        }
                    }
                }
            }
        }
        return $isBlockHaveInfoblockWithDescription;
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




                    if ($currentInfoblock && $currentInfoblock['description'] && $currentInfoblock['description'] !== ' ') {
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
            if (isset($group['value'])) {
                foreach ($group['value'] as $infoblock) {
                    $result['infoblocks'] += 1;
                }
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
                if ($infoblock['shortDescription'] && $infoblock['shortDescription']  !== '') {
                    $cell->addText($infoblock['name'], $titleStyle, $paragraphStyle);
                    $cell->addText($infoblock['shortDescription'], $textStyle, $paragraphStyle);
                    if ($tableType == 'table') {
                        $cell->addText('', $textStyle, $paragraphStyle);
                    }
                }


                break;
            case 2:
            case 3:
                if ($infoblock['descriptionForSale'] && $infoblock['descriptionForSale']  !== '') {
                    $cell->addText($infoblock['name'], $titleStyle, $paragraphStyle);
                    $cell->addText($infoblock['descriptionForSale'], $textStyle, $paragraphStyle);
                    if ($tableType == 'table') {
                        $cell->addTextBreak(1);
                    }
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
                                            $table->addRow(600);
                                            foreach ($product['cells'] as $cell) {

                                                $this->getPriceCell(false, false, $table, $styles, $cell, $contentWidth, $isHaveLongPrepayment, $numCells);
                                            }
                                        }
                                    }
                                }
                            }
                        }
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

            if ($withTotal) {
                $this->getTotalPriceRow($allPrices, $table, $styles, $contentWidth, $isHaveLongPrepayment, $numCells);
                $section->addTextBreak(3);
                // $totalSum = $allPrices['general'][0]['cells'];
                $textTotalSum = $this->getTotalSum($section, $styles, 'offer', $allPrices['general'], true);
                // $section->addText($textTotalSum, $styles['fonts']['text']['normal'],  $styles['paragraphs']['head'], $styles['paragraphs']['align']['right']);
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
        $without = 0.5;
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

            if ($code == 'discountprecent') {
                $cellValue = $priceCell['value'];
                $variableFloat = floatval($cellValue);

                // Выполняем расчет
                $result = 100 - (100 * $variableFloat);

                // Округляем до двух знаков после запятой
                $cellValue = round($result, 2);
            }
            if ($code == 'quantity' || $code == 'prepayment') {
                $cellValue = $priceCell['value'];
                $variableFloat = floatval($cellValue);

                // Округляем до двух знаков после запятой
                $cellValue = round($variableFloat, 2);
            }


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

            $cell = $table->addCell(
                $outerWidth,
                [
                    ...$outerCellStyle,
                    ...$tableStyle['valign']['center']
                ]
            );
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
                                // log::info('getSort : product', ['product' => $product]);
                                $filtredCells = array_filter($product['cells'], function ($prc) {
                                    return $prc['isActive'] == true || $prc['code'] == 'measure';
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
        $section,
        $styles,
        $documentType, // offer | invoice
        $allPrices, //products general || [alternative[0]]
        $isString,
    ) {
        $fonts = $styles['fonts'];
        $paragraphs = $styles['paragraphs'];
        $paragraphStyle  = [...$paragraphs['general'], ...$paragraphs['align']['left']];
        $paragraphTitleStyle  = [...$paragraphs['head'], ...$paragraphs['align']['center']];
        $paragraphTotalRight  = [...$paragraphs['head'], ...$paragraphs['align']['right']];
        $result = false;
        // $totalCells =  $allPrices['total'][0]['cells'];
        $sum = 0;
        $quantityMeasureString = '';
        foreach ($allPrices as $product) {
            foreach ($product['cells'] as $cell) {
                if ($cell['code'] === 'prepaymentsum') {
                    $sum = $sum + $cell['value'];
                }
                if ($cell['code'] === 'quantity' && $cell['value']) {
                    $quantityMeasureString = $cell['value'];
                }
                if ($cell['code'] === 'measure' && $cell['value']) {
                    if ($cell['isActive']) {

                        $quantityMeasureString = $quantityMeasureString . '' . $cell['value'];
                    }
                }
            }
        }

        $result = MoneySpeller::spell($sum, MoneySpeller::RUBLE);
        $firstChar = mb_strtoupper(mb_substr($result, 0, 1, "UTF-8"), "UTF-8");
        $restOfText = mb_substr($result, 1, mb_strlen($result, "UTF-8"), "UTF-8");






        $text = $firstChar . $restOfText . ' без НДС';
        $textTotalSum = $text;
        // }

        if ($documentType == 'offer') {
            $totalTextRun = $section->addTextRun($paragraphStyle);
            $totalTextRun->addText(
                'Итого: ',
                $styles['fonts']['text']['spanBold'],


            );
            $totalTextRun->addText(
                $textTotalSum,
                $styles['fonts']['text']['span'],

            );

            $totalTextRun->addText(
                'за: ',
                $styles['fonts']['text']['span'],
            );

            $totalTextRun->addText(
                $quantityMeasureString,
                $styles['fonts']['text']['spanBold'],

            );
        } else {
            $totalTextRun = $section->addTextRun($paragraphTotalRight);
            $totalTextRun->addText(
                'Итого: ',
                $styles['fonts']['text']['spanBold'],


            );
            $totalTextRun->addText(
                $textTotalSum,
                $styles['fonts']['text']['span'],

            );
        }

        return $section;
    }

    protected function getHeader($section, $styles, $providerRq, $isTwoLogo)
    {
        //HEADER
        $header = $section->addHeader();


        // create header

        $tableHeader = $header->addTable();
        $tableHeader->addRow();
        $fullWidth = $styles['page']['pageSizeW'];
        $marginRight = $section->getStyle()->getMarginRight();
        $marginLeft = $section->getStyle()->getMarginLeft();
        $contentWidth = $fullWidth - $marginLeft - $marginRight;


        $headerRqWidth = $contentWidth * 0.45;

        $headerLogoWidth = $contentWidth * 0.55;

        $headerTextStyle = $styles['fonts']['text']['small'];
        $headerRqParagraf = $styles['paragraphs']['general'];
        $cell = $tableHeader->addCell($headerRqWidth);

        if (!$isTwoLogo) {
            $shortCompanyName = $this->getShortCompanyName($providerRq['fullname']);
            $first = $shortCompanyName;
            if ($providerRq['inn']) {
                $first = $first . ' \n ' . ' , ИНН: ' . $providerRq['inn'] . ', ';
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
        } else {

            $logo =  null;
            if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos']) && count($providerRq['logos']) > 1) {

                $logo =  $providerRq['logos'][1];
            }
            if ($logo) {

                $fullPath = storage_path('app/' . $logo['path']);
                if (file_exists($fullPath)) {
                    // Добавление изображения в документ PHPWord
                    $cell->addImage(
                        $fullPath,
                        [
                            ...$styles['header']['logo'],
                            ...$styles['alignment']['start']
                        ]

                    );
                }
            }
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
                    [
                        ...$styles['header']['logo'],
                        ...$styles['alignment']['end']
                    ]
                );
            }
        }

        return $section;
    }


    protected function getDoubleHeader($section, $styles, $providerRq)
    {
        //HEADER
        // $header = $section->addHeader();


        // create header
        $fullWidth = $styles['page']['pageSizeW'];
        $marginRight = $section->getStyle()->getMarginRight();
        $marginLeft = $section->getStyle()->getMarginLeft();
        $contentWidth = $fullWidth - $marginLeft - $marginRight;

        $tableHeader = $section->addTable();
        $tableHeader->addRow();
        $headerRqWidth = $contentWidth / 2;
        $headerLogoWidth = $contentWidth / 2;

        $headerTextStyle = $styles['fonts']['text']['small'];
        $headerRqParagraf = $styles['paragraphs']['general'];
        $alignEnd = $styles['alignment']['end'];
        $cell = $tableHeader->addCell($headerRqWidth);

        $shortCompanyName = $this->getShortCompanyName($providerRq['fullname']);
        $first = $shortCompanyName;

        if ($providerRq['inn']) {
            $first = $first . '\n , ИНН: ' . $providerRq['inn'];
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

        $second = $providerRq['primaryAdresss'];




        $rqTable = $cell->addTable();

        if ($first) {
            $rqTable->addRow();
            $rqCell = $rqTable->addCell($headerRqWidth);
            $rqCell->addText($first, $headerTextStyle, $headerRqParagraf);
        }
        $cell = $tableHeader->addCell($headerRqWidth);
        $rqTable = $cell->addTable();

        if ($second) {
            $rqTable->addRow();
            $rqCell = $rqTable->addCell($headerRqWidth);
            if ($providerRq['phone']) {
                $phone = $providerRq['phone'];
                $rqCell->addText($phone, $headerTextStyle, $headerRqParagraf);
            }
            if ($providerRq['email']) {
                $email = 'e-mail:' . $providerRq['email'];
                $rqCell->addText($email, $headerTextStyle, [
                    ...$headerRqParagraf,
                    ...$alignEnd

                ]);
            }
        }


        return $section;
    }
    protected function getFooter($section, $styles, $manager)
    {
        //FOOTER
        //data

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
            $phone = 'телефон: ' . $phone;
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

        $stampsSection = $section->addTable();
        $stampsSection->addRow(
            900,
            $styles['tables']['general']['row'],
        );
        $stampsWidth = $styles['page']['pageSizeW'];

        $cell = $stampsSection->addCell(
            $stampsWidth / 3,
            [
                ...$styles['tables']['inner']['cell'],
                ...$styles['tables']['valign']['center'],
            ]

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
            [
                ...$styles['tables']['inner']['cell'],
                ...$styles['tables']['valign']['center'],
            ]
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
            [
                ...$styles['tables']['inner']['cell'],
                ...$styles['tables']['valign']['center'],
            ]
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

    protected function getLetter($section, $styles, $documentNumber, $fields, $recipient)
    {
        //FOOTER
        //data
        // Стили для обычного и выделенного текста
        $section->addTextBreak(1);
        $titleTextStyle = $styles['fonts']['h3'];
        $letterTextStyle = [
            ...$styles['fonts']['text']['normal'],
            'lineHeight' => 1.5
        ];
        $corporateletterTextStyle = [
            ...$styles['fonts']['text']['corporate'],
            'lineHeight' => 1.5
        ];
        $recipientTextStyle = $styles['fonts']['text']['small'];


        $fullWidth = $styles['page']['pageSizeW'];
        $marginRight = $section->getStyle()->getMarginRight();
        $marginLeft = $section->getStyle()->getMarginLeft();
        $contentWidth = $fullWidth - $marginLeft - $marginRight;
        $leftAlign = $styles['paragraphs']['align']['left'];
        $rightAlign = [
            ...$styles['paragraphs']['align']['right'],
            ...$styles['paragraphs']['small'],
        ];





        $table = $section->addTable();
        $table->addRow();
        $cellWidth = $contentWidth / 2;
        $letterNumberell = $table->addCell($cellWidth);
        if ($documentNumber) {
            // $letterNumberell->addTextBreak(1);
            $letterNumberell->addText('Исх. № ' . $documentNumber, $recipientTextStyle, $leftAlign);
        }

        $letterRecipientell = $table->addCell($cellWidth);




        $companyName = '';
        $inn = '';
        $positionCase = '';
        $recipientNameCase = '';
        if ($recipient) {
            if (isset($recipient['positionCase'])) {
                if ($recipient['positionCase']) {
                    $positionCase = $recipient['positionCase'];
                    $letterRecipientell->addText($positionCase, $recipientTextStyle, $rightAlign);
                }
            }

            if (isset($recipient['companyName'])) {
                if ($recipient['companyName']) {
                    $companyName = $recipient['companyName'];
                    $letterRecipientell->addText($companyName, $recipientTextStyle, $rightAlign);
                }
            }
            if (isset($recipient['inn'])) {
                if ($recipient['inn']) {
                    $inn = 'ИНН: ' . $recipient['inn'];
                    $letterRecipientell->addText($inn, $recipientTextStyle, $rightAlign);
                }
            }

            if (isset($recipient['recipientCase'])) {
                if ($recipient['recipientCase']) {
                    $recipientCase = $recipient['recipientCase'];
                    $letterRecipientell->addText($recipientCase, $recipientTextStyle, $rightAlign);
                }
            }
        }

        // $section->addTextBreak(1);
        if (isset($recipient['recipient'])) {
            if ($recipient['recipient']) {
                $recipientName = $recipient['recipient'];
                $appeal = $this->createGreeting($recipientName);
                $nameWithAppeal = $appeal . ' ' . $recipientName;
                $section->addText($nameWithAppeal, $titleTextStyle, $styles['paragraphs']['align']['center']);
            } else {
                $section->addTextBreak(1);
            }
        } else {
            $section->addTextBreak(1);
        }



        $letterText = '';
        foreach ($fields as $field) {
            if ($field && $field['code']) {
                if (
                    $field['code'] == 'letter' || $field['bitrixTemplateId'] == 'letter'

                ) {
                    if ($field['description']) {
                        $letterText = $field['description'];
                        $letterText = str_replace("\\n", "\n", $letterText);
                    }
                }
            }
        }
        $parts = preg_split('/<color>|<\/color>/', $letterText);

        $textRun = $section->addTextRun();

        $inHighlight = false;
        foreach ($parts as $part) {
            // Разбиваем часть на подстроки по символам переноса строки
            $subparts = preg_split("/\r\n|\n|\\n|\r/", $part);
            foreach ($subparts as $subpart) {
                if ($inHighlight) {
                    // Добавление выделенного текста
                    $textRun->addText($subpart, $corporateletterTextStyle, $styles['paragraphs']['align']['both']);
                } else {
                    // Добавление обычного текста
                    $textRun->addText($subpart, $letterTextStyle, $styles['paragraphs']['align']['both']);
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


    //INVOICE

    protected function getInvoice(
        $section,
        $styles,
        $price, //массив продуктов либо general либо alternetive в каждом массиве обхекты product  у каждого пproduct есть cells
        $providerRq,
        $recipient,
        $target,
        $invoiceBaseNumber
    ) {
        $section = $this->getInvoiceTopTable($section, $styles, $providerRq);
        $section = $this->getInvoiceMain($section, $styles, $providerRq, $recipient, $invoiceBaseNumber);
        $section = $this->getInvoicePrice($section, $styles, $price, $target);

        return $section;
    }



    protected function getInvoiceTopTable($section, $styles, $providerRq)
    {
        try {
            //STYLES
            $fonts = $styles['fonts'];
            $paragraphs = $styles['paragraphs'];
            $tableStyle = $styles['tables'];


            //SIZES
            //ТАБЛИЦА ЦЕН


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
            // $comePrices = $price['cells'];
            // $sortActivePrices = $this->getSortActivePrices($comePrices);
            // $allPrices =  $sortActivePrices;



            $section->addTextBreak(1);
            //TABLE


            $fancyTableStyleName = 'InvoiceHeaderTableStyle';

            $section->addTableStyle(
                $fancyTableStyleName,
                $styles['tables']['general']['table'],
                // ...$styles['tables']['general']['row'],


                // $styles['tables']['general']['row'],
                // $styles['tables']['general']['cell']
            );


            //INVOICE TOP TABLE
            $table = $section->addTable($fancyTableStyleName);
            $table->addRow($topTableHeight / 2.5);

            $cell = $table->addCell(
                $invoiceHeaderCellWidthFirst,
                $styles['tables']['general']['cell'],


            );


            $innerTable = $cell->addTable(
                $tableStyle['inner']['table'],

            );


            $innerTable->addRow($topTableHeight / 2.5 / 2);
            $innerCell1 = $innerTable->addCell(
                $invoiceHeaderCellWidthFirstInner,
                [
                    ...$styles['tables']['inner']['cell'],
                    ...$styles['tables']['alignment']['start'],
                    ...$styles['tables']['valign']['top']
                ]
            );
            $innerCell1->addText($providerRq['bank'], $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow(
                $topTableHeight / 2.5 / 2
            );
            $innerCell2 = $innerTable->addCell(
                null,

                $styles['tables']['valign']['bottom']

            );
            $innerCell2->addText("Банк получателя", $fonts['text']['small'], $paragraphStyle);



            $cellSecond = $table->addCell(
                $invoiceHeaderCellWidthSecond,
                $styles['tables']['invoice']['table']

            );

            //BIK  TABLE

            $innerTable = $cellSecond->addTable(
                $styles['tables']['invoice']['innertable']
            );
            $innerTable->addRow($topTableHeight / 7.2);
            $innerCell1 = $innerTable->addCell(
                ($invoiceHeaderCellWidthSecond),
                [
                    // ...$styles['tables']['general']['cell'],
                    ...$styles['tables']['invoice']['cell'],

                ]

            );
            $innerCell1->addText("БИК", $fonts['text']['small'], $paragraphStyle);
            $innerTable = $cellSecond->addTable(
                $styles['tables']['invoice']['innertable']
            );
            $innerTable->addRow(
                $topTableHeight / 7.2
            );
            $innerCell2 = $innerTable->addCell(
                $invoiceHeaderCellWidthSecond,
                [
                    ...$styles['tables']['invoice']['cell'],
                    // 'cellMarginLefth' => 170,
                ]
            );
            $innerCell2->addText("Cч. №", $fonts['text']['small'], $paragraphStyle);




            //CELL THIRD 
            $cellThird = $table->addCell($invoiceHeaderCellWidthThird, $styles['tables']['general']['table']);
            $innerTable = $cellThird->addTable(
                $tableStyle['inner']['table']
            );



            $innerTable->addRow($topTableHeight / 7.2);
            $innerCell1 = $innerTable->addCell(
                $invoiceHeaderCellWidthFirstInner,
                [
                    ...$styles['tables']['inner']['cell'],
                    ...$styles['tables']['alignment']['start'],
                    ...$styles['tables']['valign']['top']
                ]
            );
            $innerCell1->addText($providerRq['bik'], $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow(
                $topTableHeight / 7.2
            );
            $innerCell2 = $innerTable->addCell(
                $invoiceHeaderCellWidthFirstInner,
                [
                    ...$styles['tables']['inner']['cell'],
                    ...$styles['tables']['alignment']['start'],
                    ...$styles['tables']['valign']['top']
                ]

            );
            $innerCell2->addText($providerRq['ks'], $fonts['text']['small'], $paragraphStyle);




            //TWO ROW

            $table->addRow($topTableHeight / 9);
            $cell = $table->addCell(
                $invoiceHeaderCellWidthFirst,
                $styles['tables']['invoice']['table']
            );
            $innerTable = $cell->addTable(
                $styles['tables']['invoice']['cell']
            );
            $innerTable->addRow($topTableHeight / 9, ['cellMargin' => 0, 'cellSpacing' => 0]);
            // $table->addCell($invoiceHeaderCellWidthFirst,  $styles['tables']['general']['table']);
            $cell = $innerTable->addCell(
                300,
                $styles['tables']['invoice']['inn']
            );
            $cell->addText("ИНН", $fonts['text']['small'], $paragraphStyle);
            $cell = $innerTable->addCell(
                1500,
                $styles['tables']['invoice']['inn']
            );
            $cell->addText($providerRq['inn'], $fonts['text']['small'], $paragraphStyle);
            $cell = $innerTable->addCell(
                300,
                $styles['tables']['invoice']['inn']
            );
            $cell->addText("КПП", $fonts['text']['small'], $paragraphStyle);
            $cell = $innerTable->addCell(
                ($invoiceHeaderCellWidthFirst - 2100),
                $styles['tables']['invoice']['cell']
            );
            if (isset($providerRq['kpp'])) {
                $cell->addText($providerRq['kpp'], $fonts['text']['small'], $paragraphStyle);
            }






            $cell = $table->addCell( //outer cell
                $invoiceHeaderCellWidthSecond,
                $styles['tables']['invoice']['inn']
            );
            $innerTable = $cell->addTable(              //inner table
                $styles['tables']['invoice']['cell']
            );
            $innerTable->addRow($topTableHeight / 9);       //inner table row
            // $table->addCell($invoiceHeaderCellWidthFirst,  $styles['tables']['general']['table']);
            $cell = $innerTable->addCell(                      //inner table cell
                $invoiceHeaderCellWidthSecond,
                $styles['tables']['invoice']['cell']

            );
            $cell->addText("Сч. №", $fonts['text']['small'], $paragraphStyle);






            $cell = $table->addCell(
                $invoiceHeaderCellWidthThird,
                $styles['tables']['invoice']['inn']
            );


            $innerTable = $cell->addTable(
                $styles['tables']['invoice']['cell']
            );
            $innerTable->addRow($topTableHeight / 9);
            // $table->addCell($invoiceHeaderCellWidthFirst,  $styles['tables']['general']['table']);
            $cell = $innerTable->addCell(
                $invoiceHeaderCellWidthThird
                // $styles['tables']['invoice']['cell']
            );
            $cell->addText("40802810826000050639", $fonts['text']['small'], $paragraphStyle); //////




            //THREE ROW
            $rowHeight = ($topTableHeight / 2.5) - ($topTableHeight / 9);
            $table->addRow($rowHeight);
            // $table->addCell($invoiceHeaderCellWidthFirst,  $styles['tables']['general']['table']);
            $cell = $table->addCell(
                $invoiceHeaderCellWidthFirst,
                $styles['tables']['general']['cell']
            );
            $innerTable = $cell->addTable(
                $tableStyle['inner']['table'],
            );





            $innerTable->addRow($rowHeight / 2);
            $innerCell1 = $innerTable->addCell(
                $invoiceHeaderCellWidthFirstInner,
                [
                    ...$styles['tables']['inner']['cell'],
                    ...$styles['tables']['alignment']['start'],
                    ...$styles['tables']['valign']['top']
                ]
            );
            $innerCell1->addText("ИП Савчук А.В.", $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow($rowHeight  / 2);
            $innerCell2 = $innerTable->addCell(
                $invoiceHeaderCellWidthFirst,
                $styles['tables']['valign']['bottom']
            );
            $innerCell2->addText("Получатель", $fonts['text']['small'], $paragraphStyle);



            $cellSecond = $table->addCell(
                $invoiceHeaderCellWidthSecond,
                [
                    ...$styles['tables']['invoice']['inn'],
                    ...$styles['tables']['invoice']['bottom']
                ]
            );
            $innerTable = $cellSecond->addTable();
            $innerTable->addRow($topTableHeight / 4);
            $innerCell1 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell1->addText("", $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow();
            $innerCell2 = $innerTable->addCell(
                $invoiceHeaderCellWidthSecond,
                // $styles['tables']['invoice']['bottom']
            );
            $innerCell2->addText("", $fonts['text']['small'], $paragraphStyle);


            $cellThird = $table->addCell(
                $invoiceHeaderCellWidthThird,
                [
                    ...$styles['tables']['invoice']['inn'],
                    ...$styles['tables']['invoice']['bottom']
                ]
            );
            $innerTable = $cellThird->addTable();
            $innerTable->addRow($topTableHeight / 4);
            $innerCell1 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell1->addText("", $fonts['text']['small'], $paragraphStyle);
            $innerTable->addRow();
            $innerCell2 = $innerTable->addCell($invoiceHeaderCellWidthSecond);
            $innerCell2->addText("", $fonts['text']['small'], $paragraphStyle);



            return $section;
        } catch (\Throwable $th) {
            return $section;
        }
    }

    protected function getInvoiceMain($section, $styles, $providerRq, $recipient, $invoiceBaseNumber)
    {

        //data
        // code: "recipient"
        // companyAdress: ""
        // companyName: ""
        // inn: ""
        // position: ""
        // positionCase: ""
        // recipient: ""
        // recipientCase: ""
        // type: ""

        //provider rq
        $myCompanyName = $providerRq['fullname'];
        $myInn = $providerRq['inn'];
        $myCompanyAdress = $providerRq['registredAdress'];
        $myCompanyPhone = $providerRq['phone'];

        //recipient
        $companyName = '';
        $inn = '';
        $companyAdress = '';
        $isRecipientHave = false;
        foreach ($recipient as $key => $value) {
            if ($key == 'companyName' && $value) {
                $companyName = $value;
                $isRecipientHave = true;
            }
            if ($key == 'inn' && $value) {
                $inn = $value;
            }
            if ($key == 'companyAdress' && $value) {
                $companyAdress = $value;
            }
        }



        //styles
        $fonts = $styles['fonts'];
        $paragraphs = $styles['paragraphs'];
        $tableStyle = $styles['tables'];
        $fullWidth = $styles['page']['pageSizeW'];
        $marginRight = $styles['page']['marginLeft'];
        $marginLeft = $styles['page']['marginRight'];
        $contentWidth = ($fullWidth - $marginLeft - $marginRight);

        $paragraphTitleStyle  = [...$paragraphs['head'], ...$paragraphs['align']['center']];
        $paragraphTextStyle  = [...$paragraphs['general'], ...$paragraphs['align']['left']];
        $section->addTextBreak(3);

        $section->addText(
            'Счет на оплату N ' . $invoiceBaseNumber,
            $fonts['h2'],
            $paragraphTitleStyle

        );
        $section->addTextBreak(2);
        $table = $section->addTable();
        $table->addRow();
        $cell = $table->addCell(
            $contentWidth,
            $tableStyle['inner']['cell'],

        );



        $innerTable = $cell->addTable();

        //Поставщик
        $innerTable->addRow();
        $innerCell = $innerTable->addCell(
            $contentWidth,
            $tableStyle['inner']['cell'],

        );
        $textrun = $innerCell->addTextRun();

        $textrun->addText(
            'Поставщик: ',
            $fonts['text']['bold'],
            $paragraphs['general'],
            $paragraphs['align']['left']
        );

        if ($myCompanyName) {
            $textrun->addText(
                $myCompanyName,
                $fonts['text']['normal'],
                $paragraphs['general'],
                $paragraphs['align']['left']
            );
        }

        if ($myInn) {
            $textrun->addText(
                ', ИНН: ' . $myInn,
                $fonts['text']['normal'],
                $paragraphs['general'],
                $paragraphs['align']['left']
            );
        }

        if ($myCompanyAdress) {
            $textrun->addText(
                ', адрес:' . $myCompanyAdress,
                $fonts['text']['normal'],
                $paragraphs['general'],
                $paragraphs['align']['left']
            );
        }

        if ($myCompanyPhone) {
            $textrun->addText(
                ', телефон:' . $myCompanyPhone,
                $fonts['text']['normal'],
                $paragraphs['general'],
                $paragraphs['align']['left']
            );
        }



        //Покупатель
        if ($isRecipientHave) {
            $innerTable->addRow();
            $innerCell = $innerTable->addCell(
                $contentWidth,
                $tableStyle['inner']['cell'],

            );
            $textrun = $innerCell->addTextRun();



            $textrun->addText(
                'Покупатель: ',
                $fonts['text']['bold'],
                $paragraphs['general'],
                $paragraphs['align']['left']
            );

            if ($companyName) {
                $textrun->addText(
                    $companyName,
                    $fonts['text']['normal'],
                    $paragraphs['general'],
                    $paragraphs['align']['left']
                );
            }

            if ($inn) {
                $textrun->addText(
                    ', ИНН ' . $inn,
                    $fonts['text']['normal'],
                    $paragraphs['general'],
                    $paragraphs['align']['left']
                );
            }

            if ($companyAdress) {
                $textrun->addText(
                    ', ' . $companyAdress,
                    $fonts['text']['normal'],
                    $paragraphs['general'],
                    $paragraphs['align']['left']
                );
            }
        }
        return $section;
    }

    protected function getInvoicePrice(
        $section,
        $styles,
        $price, //массив продуктов либо general либо alternetive в каждом массиве обхекты product  у каждого пproduct есть cells 
        $target
    ) {

        //data



        // $section->addPageBreak();
        $fonts = $styles['fonts'];
        $paragraphs = $styles['paragraphs'];
        $paragraphStyle  = [...$paragraphs['general'], ...$paragraphs['align']['left']];
        $paragraphTitleStyle  = [...$paragraphs['head'], ...$paragraphs['align']['center']];
        $paragraphTotalStyle  = [...$paragraphs['head'], ...$paragraphs['align']['right']];
        $textStyle = $fonts['text']['normal'];
        $titleStyle = $fonts['text']['bold'];
        //ТАБЛИЦА ЦЕН
        $isHaveLongPrepayment = false;



        $section->addTextBreak(1);
        // $section->addText(
        //     'Стоимость',
        //     $fonts['h1'],
        //     $paragraphTitleStyle
        // );

        $fancyTableStyleName = 'DocumentPrice';


        $fullWidth = $styles['page']['pageSizeW'];
        $marginRight = $section->getStyle()->getMarginRight();
        $marginLeft = $section->getStyle()->getMarginLeft();
        $contentWidth = $fullWidth - $marginLeft - $marginRight;

        $paragraphTitleStyle  = [...$paragraphs['head'], ...$paragraphs['align']['center']];
        $paragraphTextStyle  = [...$paragraphs['general'], ...$paragraphs['align']['left']];






        if ($price[0]) {
            $numCells = count($price[0]['cells']); // Количество столбцов


            $fancyTableStyleName = 'TableStyle';

            $section->addTableStyle(
                $fancyTableStyleName,
                $styles['tables']['general']['table'],
                $styles['tables']['general']['row']
            );
            $table = $section->addTable($fancyTableStyleName);

            $table->addRow(400);

            $count = 0;
            //TABLE HEADER
            foreach ($price[0]['cells'] as $priceCell) {
                if (
                    $priceCell['code'] !== 'default' &&
                    $priceCell['code'] !== 'default_month' &&
                    $priceCell['code'] !== 'discount' &&
                    $priceCell['code'] !== 'discountprecent' &&
                    $priceCell['code'] !== 'discount_amount'
                ) {
                    $this->getInvoicePriceCell(true, false, $table, $styles, $priceCell, $contentWidth, $isHaveLongPrepayment, $numCells);
                    $count += 1;
                }
            }

            //TABLE BODY
            // foreach ([$price] as $target) {
            // if ($target) {
            // if (is_array($target) && !empty($target)) {
            foreach ($price as $product) {

                if ($product) {
                    if (is_array($product) && !empty($product) && is_array($product['cells']) && !empty($product['cells'])) {
                        $table->addRow(600);
                        foreach ($product['cells'] as $cell) {
                            if (
                                $cell['code'] !== 'default' &&
                                $cell['code'] !== 'default_month' &&
                                $cell['code'] !== 'discount' &&
                                $cell['code'] !== 'discountprecent' &&
                                $cell['code'] !== 'discount_amount'
                            ) {
                                $this->getInvoicePriceCell(false, false, $table, $styles, $cell, $contentWidth, $isHaveLongPrepayment, $numCells);
                            }
                        }
                    }
                }
            }
            // }
            // }
            // }

            // $this->getTotalPriceRow($price, $table, $styles, $contentWidth, $isHaveLongPrepayment, $numCells);
            $section->addTextBreak(1);

            $textTotalSum = $this->getTotalSum($section, $styles, 'invoice', $price, true);
        }

        return $section;
    }
    protected function getInvoicePriceCell(
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
        $cellWidth = $contentWidth / 4;
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
            if ($code == 'quantity' || $code == 'prepayment') {
                $cellValue = $priceCell['value'];
                $variableFloat = floatval($cellValue);

                // Округляем до двух знаков после запятой
                $cellValue = round($variableFloat, 2);
            }


            if ($isHeader) {
                $cellValue = $priceCell['name'];
                switch ($code) {
                    case 'quantity': //Количество
                        $cellValue = 'Количество';
                        break;
                    case 'prepaymentsum':  // При внесении предоплаты от
                        $cellValue = 'Сумма';
                        break;
                }
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

            $cell = $table->addCell(
                $outerWidth,
                [
                    ...$outerCellStyle,
                    ...$tableStyle['valign']['center']
                ]
            );
            $innerTable = $cell->addTable($tableStyle['inner']['table']);
            $innerTable->addRow();
            $innerTableCell = $innerTable->addCell(
                $innerWidth,
                $tableStyle['inner']['cell']

            )
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


    //SET IN BITRIX

    public function setTimeline($domain, $dealId, $commentLink, $commentText)
    {
        $method = '/crm.timeline.comment.add';
        // $bitrixController = new BitrixController();
        // $resultTex = "<a href=\\" . $commentLink . "\>" . $commentText . "</a>";
        $resultText = "<a href=\"" . htmlspecialchars($commentLink) . "\">" . htmlspecialchars($commentText) . "</a>";

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



    //UTILS

    public function getShortCompanyName($companyName)
    {
        $pattern = "/общество\s+с\s+ограниченной\s+ответственностью/ui";
        $patternIp = "/индивидуальный\s+предприниматель/ui";

        $shortenedPhrase = preg_replace($pattern, "ООО", $companyName);
        $shortCompanyName = preg_replace($patternIp, "ИП", $shortenedPhrase);

        return $shortCompanyName;
    }

    protected function shortenNameWithCase($name)
    {
        $parts = explode(' ', $name);
        switch (count($parts)) {
            case 3:
                return $parts[0] . ' ' . mb_substr($parts[1], 0, 1) . '. ' . mb_substr($parts[2], 0, 1) . '.';
            case 2:
                return $parts[0] . ' ' . mb_substr($parts[1], 0, 1) . '.';
            case 1:
                return $parts[0];
            default:
                return $name;
        }
    }

    protected function createGreeting($name)
    {
        $greeting = null;
        $parts = explode(' ', $name);

        // Определение пола по отчеству, если оно есть
        $gender = count($parts) === 3 ? detectGender($parts[2], 'ru') : null;
        if ($gender) {
            $greeting = $gender === Gender::MALE ? "Уважаемый " : "Уважаемая ";

            // Формирование обращения
            if (count($parts) >= 2) {
                $greeting .= $parts[1] . (isset($parts[2]) ? " " . $parts[2] : "") . "!";
            } else {
                $greeting .= $parts[0] . "!";
            }
        }

        return $greeting;
    }
}
