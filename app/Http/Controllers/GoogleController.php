<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use Exception;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Docs;
use Google\Service\Docs\BatchUpdateDocumentRequest;
use Google\Service\Docs\CreateHeaderRequest;
use Google\Service\Docs\InsertTableRequest;
use Google\Service\Docs\InsertTextRequest;
use Google\Service\Drive;

class GoogleController extends Controller
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
            // 'page' => [
            //     'pageSizeW' => Converter::inchToTwip(8.5), // ширина страницы
            //     'pageSizeH' => Converter::inchToTwip(11),   // высота страницы
            //     'marginLeft' => Converter::inchToTwip(0.5),
            //     'marginRight' => Converter::inchToTwip(0.5),
            // ],

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
    public function documentCreate($data)
    {
        $client = new Client();
        $driveService = new Drive($client);
        $client->setApplicationName('April App');
        $client->setScopes([
            Docs::DOCUMENTS,
            Drive::DRIVE // Добавьте этот scope
        ]);
        $client->setAuthConfig(env('GOOGLE_DOCS_PATH'));

        $service = new Docs($client);

        // Создание нового документа
        $document = new Docs\Document();

        $document->setTitle('Новый документ');
        $createdDocument = $service->documents->create($document);
        // Получение ID созданного документа
        $documentId = $createdDocument->documentId;



        //DOCUMENT STYLE
        $requests = [
            new Docs\Request([
                'updateDocumentStyle' => [
                    'documentStyle' => [
                        'pageSize' => [
                            'width' => 1900,
                            // 'height' => $newHeightInPoints,
                        ],
                        // Опционально: изменение полей
                        // 'marginBottom' => $marginInPoints,
                        // 'marginTop' => $marginInPoints,
                        // 'marginLeft' => $marginInPoints,
                        // 'marginRight' => $marginInPoints,
                    ],
                    'fields' => 'pageSize,marginBottom,marginTop,marginLeft,marginRight',
                ]
            ]),
        ];








        if ($data &&  isset($data['template'])) {
            $template = $data['template'];
            if ($template && isset($template['id'])) {


                $templateId = $template['id'];
                $domain = $data['template']['portal'];
                $dealId = $data['dealId'];



                //get counter test 
                $counter = Counter::whereHas('templates', function ($query) use ($templateId) {
                    $query->where('templates.id', $templateId);
                })->first();


                //document number
                $documentNumber = CounterController::getCount($templateId, 'org');

                // /invoice base number
                $step1 = preg_replace_callback(
                    '/([а-яА-Яa-zA-Z])\W+/u',
                    function ($matches) {
                        return $matches[1]; // Возвращает найденную букву без следующих за ней символов
                    },
                    $documentNumber
                );

                // Затем удаляем нежелательные символы в конце строки, если они не являются цифрами
                $invoiceBaseNumber =  preg_replace('/\D/', '', $documentNumber);
                // preg_replace('/\W+$/u', '', $step1);





                //Data
                $templateType = $data['template']['type'];


                //header-data
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
                if (count($providerRq['stamps'])) {
                    foreach ($providerRq['stamps'] as $stamp) {
                        if (isset($stamp['path'])) {
                            if ($stamp['path']) {
                                $withStamps = true;
                            }
                        }
                    }
                }

                // STYLES
                $styles = $this->documentStyle;
            }
        }









        // Формирование URL-адреса для доступа к документу
        $documentUrl = 'https://docs.google.com/document/d/' . $documentId . '/edit';
        $permission = new Drive\Permission();

        $permission->setType('anyone');
        $permission->setRole('writer');

        $this->documentHeaderCreate($service, $documentId,  $styles, $providerRq, $isTwoLogo);
        // $this->documentLetterCreate($service, $documentId, $documentNumber, $fields, $recipient);

        $this->createTableAndInsertText($service, $documentId, 'textToInsert');



        try {
            $driveService->permissions->create($documentId, $permission);
            // echo "Разрешение на доступ добавлено.";
        } catch (Exception $e) {
            echo 'Произошла ошибка: ',  $e->getMessage(), "\n";
        }
        return APIController::getSuccess([
            'price' => $price,
            'link' => $documentUrl,
            'documentNumber' => $documentNumber,
            'counter' => $counter,

        ]);
        // return $documentUrl;
    }



    //DOCUMENT SECTIONS
    public function documentHeaderCreate($service, $documentId,  $styles, $providerRq, $isTwoLogo)
    {
        $imageUrl = "";
        $logo =  null;
        if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos'])) {
            $logo =  $providerRq['logos'][0];
        }
        if ($logo) {
            $fullPath = storage_path('app/' . $logo['path']);
            if (file_exists($fullPath)) {
                $imageUrl = $fullPath;
            }
        }
        $headerTextStyle = $styles['fonts']['text']['small'];
        $headerRqParagraf = $styles['paragraphs']['general'];
        $createHeaderRequest = new CreateHeaderRequest();
        $createHeaderRequest->setType('DEFAULT'); // Указываем тип хедера как DEFAULT

        // Создание хедера
        $requests = [
            new Docs\Request([
                'createHeader' => $createHeaderRequest
            ])
        ];


        $batchUpdateRequest = new Docs\BatchUpdateDocumentRequest(['requests' => $requests]);
        $response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
        $headerId = $response->replies[0]->createHeader->headerId;
        $headerText = '';
        $requests = [];



        if (!$isTwoLogo) {
            $first = $providerRq['fullname'];
            if ($providerRq['inn']) {
                $first = $first . ', ИНН: ' . $providerRq['inn'] . ', ';
            }
            if ($providerRq['kpp']) {
                $first = $first . ', КПП: ' . $providerRq['kpp'] . ', ';
            }
            $second = $providerRq['primaryAdresss'];
            if ($providerRq['phone']) {
                $second = $second . ', \n ' . $providerRq['phone'];
            }
            if ($providerRq['email']) {
                $second = $second . ', ' . $providerRq['email'];
            }



            if ($first) {
                // $headerText = $first;
                array_push(
                    $requests,
                    new Docs\Request([
                        'insertText' => [
                            'location' => [
                                'segmentId' => $headerId,
                                'index' => 0, // Индекс должен быть 1, если вы хотите начать с начала хедера
                            ],
                            'text' => $first
                        ]
                    ])
                );
            }
            if ($second) {
                array_push(
                    $requests,
                    new Docs\Request([
                        'insertText' => [
                            'location' => [
                                'segmentId' => $headerId,
                                'index' => 0, // Индекс должен быть 1, если вы хотите начать с начала хедера
                            ],
                            'text' => $second
                        ]
                    ])
                );
                // $headerText = $headerText . ' ' . $second;
            }
        } else {

            // $logo =  null;
            // if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos']) && count($providerRq['logos']) > 1) {

            //     $logo =  $providerRq['logos'][1];
            // }
            // if ($logo) {

            //     $fullPath = storage_path('app/' . $logo['path']);
            //     if (file_exists($fullPath)) {
            //         // Добавление изображения в документ PHPWord
            //         $cell->addImage(
            //             $fullPath,
            //             [
            //                 ...$styles['header']['logo'],
            //                 ...$styles['alignment']['start']
            //             ]

            //         );
            //     }
            // }
        }
        // Пример добавления текста в хедер
        // $requests = [
        //     new Docs\Request([
        //         'insertText' => [
        //             'location' => [
        //                 'segmentId' => $headerId,
        //                 'index' => 0, // Индекс должен быть 1, если вы хотите начать с начала хедера
        //             ],
        //             'text' => $headerText
        //         ]
        //     ]),
        // new Docs\Request([
        //     'insertInlineImage' => [
        //         'uri' => $imageUrl,
        //         'location' => [
        //             'segmentId' => $headerId,
        //             // Указываем индекс вставки изображения. Это должно быть после текста
        //             'index' => strlen("Название компании\nАдрес: ...\nТелефон: ") + 1,
        //         ],
        //         'objectSize' => [
        //             'height' => [
        //                 'magnitude' => 50,
        //                 'unit' => 'PT'
        //             ],
        //             'width' => [
        //                 'magnitude' => 50,
        //                 'unit' => 'PT'
        //             ],
        //         ],
        //     ]
        // ])
        // ];

        $service->documents->batchUpdate($documentId, new Docs\BatchUpdateDocumentRequest(['requests' => $requests]));
    }

    protected function documentLetterCreate($service, $documentId, $documentNumber, $fields, $recipient)
    {
        $requests = [];

        // Добавление текста с номером документа, если он есть
        if (!empty($documentNumber)) {
            $requests[] =  new Docs\Request([
                'insertText' => new Docs\InsertTextRequest([
                    'location' => [
                        'index' => 1,
                    ],
                    'text' => "Исх. № " . $documentNumber . "\n"
                ])
            ]);
        }

        // Предположим, что $styles содержит настройки стилей
        // $titleTextStyle = $styles['fonts']['h3'];
        // $letterTextStyle = $styles['fonts']['text']['normal'];

        // Добавление текста с информацией о получателе
        $recipientText = "";
        if (!empty($recipient['companyName'])) {
            $recipientText .= $recipient['companyName'] . "\n";
        }
        if (!empty($recipient['inn'])) {
            $recipientText .= "ИНН: " . $recipient['inn'] . "\n";
        }
        // Продолжить добавление других полей...

        if (!empty($recipientText)) {
            $requests[] = new Docs\Request([
                'insertText' => new Docs\InsertTextRequest([
                    'location' => [
                        'index' => 1,
                    ],
                    'text' => $recipientText
                ])
            ]);
        }

        // Добавление таблицы (как пример, таблица 2x2)
        $requests[] = new Docs\Request([
            'insertTable' => new Docs\InsertTableRequest([
                'rows' => 2,
                'columns' => 2,
                'location' => [
                    'index' => 1,
                ],
            ])
        ]);

        // Пример добавления дополнительного текста (тела письма)
        $letterText = "";
        foreach ($fields as $field) {
            if ($field['code'] == 'letter' || $field['bitrixTemplateId'] == 'letter') {
                $letterText .= $field['description'] . "\n";
            }
        }

        if (!empty($letterText)) {
            $requests[] = new Docs\Request([
                'insertText' => new Docs\InsertTextRequest([
                    'location' => [
                        'index' => 1,
                    ],
                    'text' => $letterText
                ])
            ]);
        }

        // Выполнение запроса
        $batchUpdateRequest = new Docs\BatchUpdateDocumentRequest([
            'requests' => $requests
        ]);

        try {
            $response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
            return $response;
        } catch (Exception $e) {
            echo 'Ошибка при добавлении раздела в документ: ',  $e->getMessage(), "\n";
        }
    }



    // UTILITS
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

    protected  function createTableAndInsertText($service, $documentId, $textToInsert)
    {
        // Создание запроса на добавление таблицы из двух столбцов и одной строки
        $insertTableRequest = new Docs\InsertTableRequest([
            'rows' => 1,
            'columns' => 2,
            'location' => [
                'index' => 1, // Вставляем таблицу в начало документа
            ],
        ]);

        // Создание объекта запроса для добавления таблицы
        $requestForTable = new Docs\Request();
        $requestForTable->setInsertTable($insertTableRequest);

        // Создание запроса на добавление текста. 
        // Обратите внимание: реальный индекс для вставки текста в ячейку таблицы 
        // потребует дополнительных вычислений и может зависеть от содержимого документа.
        // Для простоты примера используется предполагаемый индекс.
        $insertTextRequest = new Docs\InsertTextRequest([
            'location' => [
                'index' => 2, // Предполагаемый индекс для вставки текста в первую ячейку
            ],
            'text' => $textToInsert,
        ]);

        // Создание объекта запроса для добавления текста
        $requestForText = new Request();
        $requestForText->setInsertText($insertTextRequest);

        // Формирование итогового запроса на обновление документа
        $batchUpdateRequest = new BatchUpdateDocumentRequest();
        $batchUpdateRequest->setRequests([$requestForTable, $requestForText]);

        // Выполнение запроса
        try {
            $response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);
            return $response;
        } catch (Exception $e) {
            echo 'Ошибка при создании таблицы и вставке текста: ',  $e->getMessage(), "\n";
            return null;
        }
    }
}
