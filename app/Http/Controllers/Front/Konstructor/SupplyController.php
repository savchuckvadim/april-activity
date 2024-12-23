<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\ALogController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\PDFDocumentController;
use App\Http\Resources\PortalContractResource;
use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use App\Models\Portal;
use App\Models\PortalContract;
use App\Services\BitrixDealDocumentContractService;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\Line;
use PhpOffice\PhpWord\Element\Link;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;
use Ramsey\Uuid\Uuid;

class SupplyController extends Controller
{

    public function frontInit(Request $request) //by id
    {
        $data = $request->all();
        $domain = $data['domain'];
        $companyId = $data['companyId'];
        $contractType = $data['contractType']; //service | product

        $contract = $data['contract'];
        $generalContractModel = $contract['contract'];
        $contractQuantity = $generalContractModel['coefficient'];

        $productSet = $data['productSet'];
        $products = $data['products'];
        $contractProductName = $generalContractModel['productName'];
        $arows = $data['arows'];
        $total = null;
        if (!empty($data['total'])) {
            $total =  $data['total'];
            if (!empty($data['total'][0])) {
                $total =  $data['total'][0];
            }
        }

        $currentComplect = $data['complect']; //lt  //ltInPacket
        $consaltingProduct = $data['consalting']['product'];
        $lt = $data['legalTech'];
        $starProduct = $data['star']['product'];
        $documentInfoblocks =  $data['documentInfoblocks'];
        $isSupplyReport = false;
        if (!empty($data['isSupplyReport'])) {
            $isSupplyReport = true;
        }
        try {
            $portal = Portal::where('domain', $domain)->first();

            $providers = $portal->providers;
            $hook = BitrixController::getHook($domain);
            $result = [
                'providers' => $providers,
                'client' =>  [
                    'rq' => [],
                    'bank' => [],
                    'address' => [],
                ],
                'provider' => [
                    'rq' => [],
                    'bank' => [],
                    'address' => [],
                ],
                // 'contract' => $this->getContractGeneralForm($arows, $contractQuantity),
                'contract' => [],

                'specification' => $this->getSpecification(
                    $currentComplect,
                    $products,
                    $consaltingProduct,
                    $lt,
                    $starProduct,
                    $contractType, //service | product
                    $contract,

                    $arows,
                    $contractQuantity,
                    $documentInfoblocks,
                    $contractProductName,
                    $total
                ),
                'clientType' =>  [
                    'type' => 'select',
                    'name' => 'Тип клиента',
                    'value' =>  [
                        'id' => 0,
                        'code' => 'org',
                        'name' => 'Организация Коммерческая',
                        'title' => 'Организация Коммерческая'
                    ],
                    'isRequired' => true,
                    'code' => 'type',
                    'items' => [
                        [
                            'id' => 0,
                            'code' => 'org',
                            'name' => 'Организация Коммерческая',
                            'title' => 'Организация Коммерческая'
                        ],
                        [
                            'id' => 1,
                            'code' => 'org_state',
                            'name' => 'Организация Бюджетная',
                            'title' => 'Организация Бюджетная'
                        ],
                        [
                            'id' => 2,
                            'code' => 'ip',
                            'name' => 'Индивидуальный предприниматель',
                            'title' => 'Индивидуальный предприниматель'
                        ],
                        // [
                        //     'id' => 3,
                        //     'code' => 'advokat',
                        //     'name' => 'Адвокат',
                        //     'title' => 'Адвокат'
                        // ],
                        [
                            'id' => 4,
                            'code' => 'fiz',
                            'name' => 'Физическое лицо',
                            'title' => 'Физическое лицо'
                        ],

                    ],
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0,

                ],

                'currentComplect' => $currentComplect,
                'products' => $products,
                'consaltingProduct' => $consaltingProduct,
                'lt' => $lt,
                'starProduct' => $starProduct,
                'contractType' => $contractType,


            ];

            if (!empty($isSupplyReport)) {
                $result['supply'] = $this->getSupplyReportData();
            }

            $rqMethod = '/crm.requisite.list';
            $rqData = [
                'filter' => [
                    'ENTITY_TYPE_ID' => 4,
                    'ENTITY_ID' => $companyId,
                ]
            ];
            $url = $hook . $rqMethod;
            $responseData = Http::post($url,  $rqData);

            $rqResponse = BitrixController::getBitrixResponse($responseData, $rqMethod);
            $clientRq = null;
            $clientRqBank = null;
            $clientRqAddress = null;
            //bank
            if (!empty($rqResponse)) {
                $clientRq = $rqResponse[0];
                if (!empty($clientRq) && isset($clientRq['ID'])) {


                    $rqResponse  = $clientRq;
                    $rqId = $rqResponse['ID'];
                    $bankMethod = '/crm.requisite.bankdetail.list';
                    $bankData = [
                        'filter' => [
                            // 'ENTITY_TYPE_ID' => 4,
                            'ENTITY_ID' => $rqId,
                        ]
                    ];
                    $url = $hook . $bankMethod;
                    $responseData = Http::post($url,  $bankData);
                    $clientRqBank  = BitrixController::getBitrixResponse($responseData, $bankMethod);
                    if (!empty($clientRqBank)) {
                        if (isset($clientRqBank[0])) {
                            $clientRqBank = $clientRqBank[0];
                        }
                    }

                    //address
                    $addressMethod = '/crm.address.list';
                    $addressData = [
                        'filter' => [
                            'ENTITY_TYPE_ID' => 8,
                            'ENTITY_ID' =>  $rqId,
                        ]
                    ];
                    $url = $hook . $addressMethod;
                    $responseData = Http::post($url,  $addressData);
                    $clientRqAddress  = BitrixController::getBitrixResponse($responseData, $addressMethod);
                }
            }

            $client = $this->getClientRqForm($clientRq, $clientRqAddress, $clientRqBank, $contractType);
            $result['client'] = $client;
            return APIController::getSuccess(
                [
                    'init' => $result,
                    // 'addressresponse' => $result['client']['address'],
                    // 'clientRq' => $clientRq,
                    // 'clientRqBank' => $clientRqBank,
                    // 'clientRqAddress' => $clientRqAddress,
                ]
            );
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['companyId' => $companyId, 'domain' => $domain, 'products' => $products]
            );
        }
    }

    public function getDocument(Request $request)
    {

        $data = $request->all();

        return $this->getSupplyReportTempalteDocument($data);
    }


    protected function getSupplyReportTempalteDocument($data)
    {
        $domain = $data['domain'];

        $bxCompanyItems = $data['bxCompanyItems'];
        $bxContactItems = $data['bxContactItems'];
        $bxDealItems = $data['bxDealItems'];
        $supplyReport = $data['supplyReport'];


        $companyId = $data['companyId'];

        $bxCompanyLink = 'https://' . $domain . '/company/details/' . $companyId . '/';

        $companyLink = new Link($bxCompanyLink, 'Компания ' . $companyId . '');

        $dealId = null;
        if (!empty($data['dealId'])) {

            $dealId = $data['dealId'];
        }
        $contractType = $data['contractType'];

        $contract = $data['contract'];
        $contract_type = $data['contract']['aprilName'];
        $generalContractModel = $contract['contract'];
        $contractQuantity = $generalContractModel['coefficient'];

        $providerState = $data['contractProviderState'];
        $providerRq = $providerState['current']['rq'];


        $provider_fullname =  $providerRq['fullname'];



        $productSet = $data['productSet']; //все продукты rows из general в виде исходного стэйт объекта

        $products = $data['products'];  //productsFromRows  объекты продуктов с полями для договоров полученные из rows
        $contractProductName = $generalContractModel['productName']; // приставка к имени продукта из current contract
        $isProduct = $contractType !== 'service';
        $contractCoefficient = $contract['prepayment'];




        $arows = $data['arows']; //все продукты rows из general в виде массива
        $total = $productSet['total'][0];
        $totalSum = 0;
        $totalMonth = 0;

        $quantity = $total['product']['contractCoefficient'] * $total['price']['quantity'];


        foreach ($arows as $arow) {
            // $totalMonth +=  round($arow['price']['month'], 2);

            $totalSum +=  round($arow['price']['sum'], 2);
        }
        $totalMonth = round($totalSum / $quantity, 2);

        $supply = $data['supply'];
        $supplyType = $supply['type'];
        $contractGeneralFields = $data['contractBaseState']['items']; //fields array

        $contractClientState = $data['contractClientState']['client'];
        $clientRq = $contractClientState['rqs']['rq'];                //fields array
        $clientRqBank = $contractClientState['rqs']['bank'];
        $clientType = $contractClientState['type'];



        function filterByClientTypePDF($item, $clientType)
        {
            return in_array($clientType, $item['includes']);
        }

        // Фильтрация массивов с использованием array_filter
        $filteredClientRq = array_filter($clientRq, function ($item) use ($clientType) {
            return filterByClientTypePDF($item, $clientType);
        });

        $filteredClientRqBank = array_filter($clientRqBank, function ($item) use ($clientType) {
            return filterByClientTypePDF($item, $clientType);
        });

        $contractSpecification = $data['contractSpecificationState']['items'];


        $filteredcontractSpecification = array_filter($contractSpecification, function ($item) use ($contractType) {
            return  in_array($contractType, $item['contractType']);
        });
        $filteredcontractSpecification = array_filter($filteredcontractSpecification, function ($item) use ($supplyType) {
            return  in_array($supplyType, $item['supplies']);
        });



        $consaltingString = 'Горячая Линия';
        if (!empty($data['consalting'])) {
            if (!empty($data['consalting']['current'])) {
                if (!empty($data['consalting']['current']['title'])) {
                    $consaltingString = $data['consalting']['current']['title'];
                }
            }
        }

        $supply = $data['supplyReport'];

        $documentPrice = $data['documentPrice'];

        $documentNumber = 780;

        $filePath = 'app/public/konstructor/templates/supply';

        $fullPath = storage_path($filePath . '/supply_report_gsr.docx');
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);




        // $templateProcessor->setValue('documentNumber', $documentNumber);

        $templateProcessor->setValue('contract_type', $contract_type);
        $templateProcessor->setValue('provider_fullname', $provider_fullname);

        $templateProcessor->setValue('bx_deal', $companyId);


        //price
        $templateProcessor->setValue('total_sum', $totalSum);
        $templateProcessor->setValue('prepayment_sum', $totalMonth);
        $templateProcessor->setValue('prepayment_quantity', $quantity);



        foreach ($supplyReport as  $reportItem) {
            $name = $reportItem['code'];
            $value = '';

            if ($reportItem['type'] !== 'select' && $reportItem['type'] !== 'enumeration') {

                $value = $reportItem['value'];
            } else {

                if (!empty($reportItem['value']) && !empty($reportItem['items'])) {
                    foreach ($reportItem['items'] as $item) {

                        if ($item['code'] === $reportItem['value']['code']) {
                            $value = $item['name'];
                        }
                    }
                }
            }

            if (is_string($value) || is_numeric($value)) {
                $templateProcessor->setValue($name, strval($value));
            } else {
                $templateProcessor->setValue($name, ''); // или другая логика
            }
        }



        $complects = [];
        foreach ($arows as $row) {
            $name = $row['name'];
            $supply = '';
            $complect_hdd = '';
            if (!empty($row['supply'])) {
                if (!empty($row['supply']['name'])) {
                    $supply = $row['supply']['name'];
                    $complect_hdd = $row['product']['contractSupplyProp1'];
                }
            }
            array_push($complects, [
                'complect_name' => $name,
                'complect_sup' => $supply,
                'complect_hdd' => $complect_hdd
            ]);
        }

        // $templateProcessor->cloneRowAndSetValues('complect_name', $complects);

        foreach ($filteredClientRq as $rqItem) {
            $value = '';

            if ($rqItem['code'] === 'fullname') {
                $templateProcessor->setValue('client_company_name', $rqItem['value']);
            } else  if ($rqItem['code'] === 'fullname') {
                $templateProcessor->setValue('client_company_primary_address', $rqItem['value']);
            } else  if ($rqItem['code'] === 'registredAdress') {
                $templateProcessor->setValue('client_company_registred_address', $rqItem['value']);
            } else  if ($rqItem['code'] === 'primaryAdresss') {
                $templateProcessor->setValue('client_company_primary_address', $rqItem['value']);
            } else  if ($rqItem['code'] === 'inn') {
                $templateProcessor->setValue('client_inn', $rqItem['value']);
            }
        }

        foreach ($bxCompanyItems as $key => $bxCompanyItem) {
            $value = '';
            if (!empty($bxCompanyItem['current'])) {
                if (isset($bxCompanyItem['current']['name'])) {
                    $value = $bxCompanyItem['current']['name'];
                } else {
                    $value = $bxCompanyItem['current'];
                }
            }

            if (is_string($value) || is_numeric($value)) {
                $templateProcessor->setValue($key, strval($value));
            } else {
                $templateProcessor->setValue($key, '');
            }
        }

        foreach ($bxDealItems as $key => $bxDealItem) {
            $value = '';
            if (!empty($bxDealItem['current'])) {
                if (isset($bxDealItem['current']['name'])) {
                    $value = $bxDealItem['current']['name'];
                } else {
                    $value = $bxDealItem['current'];
                }
            }

            if (is_string($value) || is_numeric($value)) {
                $value = $bxDealItem['value'];
                // $value = $this->formatDateForWord($value);
                $templateProcessor->setValue($key, strval($value));
            } else {
                $templateProcessor->setValue($key, '');
            }
        }


        foreach ($contractSpecification as $cntrctSpecItem) {
            $value = '';

            if ($cntrctSpecItem['code'] === 'specification_email') {
                $templateProcessor->setValue('email_garant', $cntrctSpecItem['value']);
            } else  if ($cntrctSpecItem['code'] === 'specification_ibig') {
                $templateProcessor->setValue('complect_fields_left', $cntrctSpecItem['value']);
            } else  if ($cntrctSpecItem['code'] === 'specification_ifree') {


                $templateProcessor->setValue('complect_fields_right', $cntrctSpecItem['value']);
            } else  if ($cntrctSpecItem['code'] === 'specification_lt_free') {
                $value = '';
                if (!empty($cntrctSpecItem['value'])) {
                    $value = 'Бесплатный LT ' . $cntrctSpecItem['value'];


                    foreach ($filteredcontractSpecification as $cntrcItem) {
                        if ($cntrcItem['code'] === 'specification_lt_free_services') {
                            $value = $value . ': ' . "\n" . $cntrcItem['value'];
                        }
                    }
                }



                $templateProcessor->setValue('complect_lt_left', $value);
            } else  if ($cntrctSpecItem['code'] === 'specification_lt_packet') {
                $value = '';
                if (!empty($cntrctSpecItem['value'])) {
                    $value = $cntrctSpecItem['name'] . ' ' . $cntrctSpecItem['value'];


                    foreach ($filteredcontractSpecification as $cntrcItem) {
                        if ($cntrcItem['code'] === 'specification_lt_services') {
                            $value = $value . ': ' . "\n" . $cntrcItem['value'];
                        }
                    }
                }

                $templateProcessor->setValue('complect_lt_right', $value);
            }
        }
        $templateProcessor->setValue('complect_pk', $consaltingString);


        $hash = md5(uniqid(mt_rand(), true));
        $outputFileName = 'Отчет_о_продаже.docx';
        $outputFilePath = storage_path('app/public/clients/' . $domain . '/supplies/' . $hash);



        // Сохраняем файл Word в формате .docx
        $uid = Uuid::uuid4()->toString();
        $shortUid = substr($uid, 0, 4); // Получение первых 4 символов


        if (!file_exists($outputFilePath)) {
            mkdir($outputFilePath, 0775, true); // Создать каталог с правами доступа
        }

        // // Проверить доступность каталога для записи
        if (!is_writable($outputFilePath)) {
            throw new \Exception("Невозможно записать в каталог: $outputFilePath");
        }



        $fullOutputFilePath = $outputFilePath . '/' . $outputFileName;


        // $outputDir = dirname($outputFilePath);
        // if (!file_exists($outputDir)) {
        //     mkdir($outputDir, 0755, true);
        // }

        $templateProcessor->saveAs($fullOutputFilePath);

        // // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ

        $link =   route('download-supply-report', ['domain' => $domain,  'hash' => $hash, 'filename' => $outputFileName]);
        $document = route('supply-report', ['domain' => $domain,  'hash' => $hash, 'filename' => $outputFileName]);
        $file = route('file-supply-report', ['domain' => $domain,  'hash' => $hash, 'filename' => $outputFileName]);

        $method = '/crm.timeline.comment.add';
        $hook = BitrixController::getHook($domain);

        $url = $hook . $method;

        $message = '<a href="' . $link . '" target="_blank">' . $outputFileName . '</a>';

        $fields = [
            "ENTITY_ID" => $dealId,
            "ENTITY_TYPE" => 'deal',
            "COMMENT" => $message
        ];
        $data = [
            'fields' => $fields
        ];
        $responseBitrix = Http::get($url, $data);


        return APIController::getSuccess(
            [
                'result' => [
                    'contractData' => $data,
                    'link' => $link,
                    'document' => $document,
                    'file' => $file,
                ]
            ]
        );
    }

    protected function formatDateForWord($date): ?string
    {
        try {
            $dateTime = Carbon::parse($date);

            // Форматирование даты в зависимости от наличия времени
            if ($dateTime->format('H:i') === '00:00') {
                // Только дата
                return $dateTime->translatedFormat('j F Y');
            } else {
                // Дата и время
                return $dateTime->translatedFormat('j F Y H:i');
            }
        } catch (\Throwable $th) {
            // Если дата невалидна
            return $date;
        }
    }

    public function getSupplyDocument(Request $request)
    {
        $contractLink = '';
        $data = $request->all();
        $domain = $data['domain'];
        $companyId = $data['companyId'];
        $dealId = null;
        if (!empty($data['dealId'])) {

            $dealId = $data['dealId'];
        }
        $contractType = $data['contractType'];

        $contract = $data['contract'];
        $generalContractModel = $contract['contract'];
        $contractQuantity = $generalContractModel['coefficient'];

        $productSet = $data['productSet']; //все продукты rows из general в виде исходного стэйт объекта

        $products = $data['products'];  //productsFromRows  объекты продуктов с полями для договоров полученные из rows
        $contractProductName = $generalContractModel['productName']; // приставка к имени продукта из current contract
        $isProduct = $contractType !== 'service';
        $contractCoefficient = $contract['prepayment'];




        $arows = $data['arows']; //все продукты rows из general в виде массива
        $total = $productSet['total'][0];


        $supply = $data['supply'];
        $supplyType = $supply['type'];
        $contractGeneralFields = $data['contractBaseState']['items']; //fields array

        $contractClientState = $data['contractClientState']['client'];
        $clientRq = $contractClientState['rqs']['rq'];                //fields array
        $clientRqBank = $contractClientState['rqs']['bank'];
        $clientType = $contractClientState['type'];

        function filterByClientType($item, $clientType)
        {
            return in_array($clientType, $item['includes']);
        }

        // Фильтрация массивов с использованием array_filter
        $filteredClientRq = array_filter($clientRq, function ($item) use ($clientType) {
            return filterByClientType($item, $clientType);
        });

        $filteredClientRqBank = array_filter($clientRqBank, function ($item) use ($clientType) {
            return filterByClientType($item, $clientType);
        });

        $providerState = $data['contractProviderState'];

        $providerRq = $providerState['current']['rq'];

        $supply = $data['supplyReport'];

        $documentPrice = $data['documentPrice'];

        $documentNumber = 780;

        $documentController = new DocumentController();
        $pdfDocumentController = new PDFDocumentController();

        $price = $pdfDocumentController->getInvoicePricesData($documentPrice, true, 0);
        $generalFont = [
            'name' => 'Arial',
            'color' => '000000',
            'lang' => 'ru-RU',
            'spaceAfter' => 0,    // Интервал после абзаца
            'spaceBefore' => 5,   // Интервал перед абзацем
            'lineHeight' => 1.15,  // Высота строки
        ];

        $font = [
            'general' => [
                'name' => 'Arial',
                'color' => '000000',
                'lang' => 'ru-RU',
            ],
            'h1' => [
                ...$generalFont,
                'bold' => true,
                'size' => 16
            ],
            'h2' => [
                ...$generalFont,
                'bold' => true,
                'size' => 14
            ],
            'h3' => [
                ...$generalFont,
                'bold' => true,
                'size' => 12
            ],
            'blue' => [
                'name' => 'Arial',
                'color' => '#005fa8',
                'bold' => true,
                'size' => 9
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
                    'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::END,
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



        $contractSpecification = $data['contractSpecificationState']['items'];


        $filteredcontractSpecification = array_filter($contractSpecification, function ($item) use ($contractType) {
            return  in_array($contractType, $item['contractType']);
        });
        $filteredcontractSpecification = array_filter($filteredcontractSpecification, function ($item) use ($supplyType) {
            return  in_array($supplyType, $item['supplies']);
        });

        $document = new \PhpOffice\PhpWord\PhpWord();
        $page = [

            'pageSizeW' => Converter::inchToTwip(210 / 25.4), // ширина страницы A4 в twips
            'pageSizeH' => Converter::inchToTwip(297 / 25.4), // высота страницы A4 в twips
            'marginLeft' => Converter::inchToTwip(0.5),       // левый отступ
            'marginRight' => Converter::inchToTwip(0.5),      // правый отступ
        ];
        $section = $document->addSection($page);
        $documentController->getHeader($section, null, $providerRq, false);


        $section->addText('Отчет о продаже', $font['h1'], $font['alignment']['center']);
        $section->addText('Поставщик: ' . $providerRq['fullname'], $font['h3'], $font['alignment']['start']);
        $section->addText('Клиент: ' . $filteredClientRq[0]['value'], $font['h3'], $font['alignment']['start']);
        $section->addLink('https://' . $domain . '/crm/company/details/' . $companyId . '/', 'Компания в битрикс: ' . $companyId, $font['blue'], $font['alignment']['start']);

        $this->getTable($filteredClientRq, $section);
        $this->getTable($filteredClientRqBank, $section);
        $section->addPageBreak();

        $section->addText('Комплект', $font['h3'], $font['alignment']['start']);

        $this->getTable($filteredcontractSpecification, $section);
        $section->addPageBreak();
        $section->addText('Договор', $font['h3'], $font['alignment']['start']);

        $this->getTable($contractGeneralFields, $section);
        $section->addPageBreak();
        $this->getTable($supply, $section);
        $section->addPageBreak();
        $section->addText('', $font['h2'], $font['alignment']['start']);


        $this->getPriceTable($price['allPrices'], $section);
        //create document


        // Если последняя строка не заполнена полностью, заполняем оставшиеся ячейки пустыми


        // Сохраняем файл Word в формате .docx
        $uid = Uuid::uuid4()->toString();
        $shortUid = substr($uid, 0, 4); // Получение первых 4 символов
        $path = $data['domain'] . '/supplies/' . $data['userId'];
        $resultPath = storage_path('app/public/clients/' . $path);



        if (!file_exists($resultPath)) {
            mkdir($resultPath, 0775, true); // Создать каталог с правами доступа
        }

        // // Проверить доступность каталога для записи
        if (!is_writable($resultPath)) {
            throw new \Exception("Невозможно записать в каталог: $resultPath");
        }
        $resultFileName = 'Отчет о продаже_' . $shortUid . '.docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($document, 'Word2007');

        $objWriter->save($resultPath . '/' . $resultFileName);

        // // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ

        $link = asset('storage/clients/' . $path . '/' . $resultFileName);
        $method = '/crm.timeline.comment.add';
        $hook = BitrixController::getHook($domain);

        $url = $hook . $method;

        $message = '<a href="' . $link . '" target="_blank">' . $resultFileName . '</a>';

        $fields = [
            "ENTITY_ID" => $dealId,
            "ENTITY_TYPE" => 'deal',
            "COMMENT" => $message
        ];
        $data = [
            'fields' => $fields
        ];
        $responseBitrix = Http::get($url, $data);

        return APIController::getSuccess(
            ['contractData' => $data, 'link' => $link, 'price' =>  $price]
        );
    }

    protected function getTable($items, $section)
    {
        Carbon::setLocale('ru');

        $baseCellMargin = 30;
        $baseCellMarginSmall = 10;

        $baseBorderSize = 4;
        $page = [
            // 'pageSizeW' => Converter::inchToTwip(8.5), // ширина страницы
            // 'pageSizeH' => Converter::inchToTwip(11),   // высота страницы
            // 'marginLeft' => Converter::inchToTwip(0.5),
            // 'marginRight' => Converter::inchToTwip(0.5),

            'sizeW' => Converter::inchToTwip(210 / 25.4), // ширина страницы A4 в twips
            'sizeH' => Converter::inchToTwip(297 / 25.4), // высота страницы A4 в twips
            'marginLeft' => Converter::inchToTwip(0.2),       // левый отступ
            'marginRight' => Converter::inchToTwip(0.2),      // правый отступ
            'table' => [
                'borderSize' => $baseBorderSize,
                'borderColor' => '000000',
                // 'cellMargin' =>  $baseCellMarginSmall,
                // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                // 'cellSpacing' => 20
            ],
            'row' => [
                'cellMargin' =>  $baseCellMarginSmall,
                'borderSize' => 0,
                'bgColor' => '66BBFF',
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
            ],
            'cell' => [
                // 'valign' => 'center',
                'borderSize' => $baseBorderSize,
                // 'borderColor' => '000000',  // Цвет границы (чёрный)
                'cellMarginTop' => $baseCellMargin,
                'cellMarginRight' => $baseCellMargin,
                'cellMarginBottom' => $baseCellMargin,
                'cellMarginLeft' => $baseCellMargin,
                'valign' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
            ],
            'inner' => [
                'cell' => [

                    'borderSize' => 0,
                    'borderColor' => 'FFFFFF',
                    'cellMargin' => $baseCellMargin,
                    // 'valign' => 'bottom',
                    'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                    'cellMarginTop' => $baseCellMargin,
                    'cellMarginRight' => $baseCellMargin,
                    'cellMarginBottom' => $baseCellMargin,
                    'cellMarginLeft' => $baseCellMargin,
                    'valign' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                    // ]

                ],
                'table' => [
                    'borderSize' => 0,
                    'borderColor' => 'FFFFFF',
                    'cellMargin' => $baseCellMargin,
                    'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                    'valign' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                ],

            ],
        ];
        $fancyTableStyleName = 'TableStyle';




        $section->addTableStyle(
            $fancyTableStyleName,
            $page['table'],
            $page['row'],

        );
        $table = $section->addTable();
        // Переменная для отслеживания текущего индекса
        $contentWidth = $page['sizeW'] - $page['marginLeft'] - $page['marginRight'];
        $colWidth = $contentWidth / 2;

        $innerContentWidth = $contentWidth - 30;
        $innerColWidth = $innerContentWidth / 2;
        // Перебираем все элементы
        foreach ($items as $item) {
            // Если это первый элемент строки, создаем новую строку
            if (!empty($item['isActive'])) {
                $table->addRow();

                // Добавляем ячейку в текущую строку
                $value = $item['value'];
                if (isset($value['name'])) {
                    $value = $value['name'];
                }

                if (Carbon::hasFormat($value, 'Y-m-d')) {
                    // Преобразуем и форматируем дату
                    $value = Carbon::parse($value)->translatedFormat('j F Y');
                }


                // Добавляем ячейку с названием (title)
                $cell = $table->addCell($colWidth, $page['cell']);
                $innerTable = $cell->addTable($page['inner']['table']);
                $innerTable->addRow();
                $innerTableCell = $innerTable->addCell($innerColWidth, $page['inner']['cell'])->addText($item['name']); // Уменьшаем ширину, чтобы создать отступ
                // Добавляем ячейку со значением (value)
                $cell = $table->addCell($colWidth, $page['cell']);

                $innerTable = $cell->addTable($page['inner']['table']);
                $innerTable->addRow();
                $innerTableCell = $innerTable->addCell($innerColWidth, $page['inner']['cell'])->addText($value); // Уменьшаем ширину, чтобы создать отступ


            }
        }
    }
    protected function getPriceTable($items, $section)
    {

        $baseCellMargin = 30;
        $baseCellMarginSmall = 10;

        $baseBorderSize = 4;
        $page = [
            // 'pageSizeW' => Converter::inchToTwip(8.5), // ширина страницы
            // 'pageSizeH' => Converter::inchToTwip(11),   // высота страницы
            // 'marginLeft' => Converter::inchToTwip(0.5),
            // 'marginRight' => Converter::inchToTwip(0.5),

            'sizeW' => Converter::inchToTwip(210 / 25.4), // ширина страницы A4 в twips
            'sizeH' => Converter::inchToTwip(297 / 25.4), // высота страницы A4 в twips
            'marginLeft' => Converter::inchToTwip(0.2),       // левый отступ
            'marginRight' => Converter::inchToTwip(0.2),      // правый отступ
            'table' => [
                'borderSize' => $baseBorderSize,
                'borderColor' => '000000',
                // 'cellMargin' =>  $baseCellMarginSmall,
                // 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER,
                // 'cellSpacing' => 20
            ],
            'row' => [
                'cellMargin' =>  $baseCellMarginSmall,
                'borderSize' => 0,
                'bgColor' => '66BBFF',
                'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER
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
            'inner' => [
                'cell' => [

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
                ],

            ],
        ];
        $fancyTableStyleName = 'TableStyle';




        $section->addTableStyle(
            $fancyTableStyleName,
            $page['table'],
            $page['row'],

        );
        $table = $section->addTable();
        // Переменная для отслеживания текущего индекса
        $contentWidth = $page['sizeW'] - $page['marginLeft'] - $page['marginRight'];
        $colWidth = $contentWidth / 2;

        $innerContentWidth = $contentWidth - 30;
        $innerColWidth = $innerContentWidth / 2;
        // Перебираем все элементы

        $table->addRow();
        foreach ($items[0]['cells'] as $cell) {
            # code...


            $value = $cell['name'];
            $cell = $table->addCell($colWidth, $page['cell']);
            $innerTable = $cell->addTable($page['inner']['table']);
            $innerTable->addRow();
            $innerTableCell = $innerTable->addCell($innerColWidth, $page['inner']['cell'])->addText($value); // Уменьшаем ширину, чтобы создать отступ
        }

        foreach ($items as $key => $item) {
            // Если это первый элемент строки, создаем новую строку
            // code: "name"
            // defaultValue: "Гарант-Юрист"
            // isActive: true
            // name: "Наименование"
            // order: 0
            // target: "general"
            // type: "string"
            // value: "Гарант-Юрист Интернет 1 ОД"
            $table->addRow();
            foreach ($item['cells'] as $cell) {

                $value = $cell['value'];
                $cell = $table->addCell($colWidth, $page['cell']);
                $innerTable = $cell->addTable($page['inner']['table']);
                $innerTable->addRow();
                $innerTableCell = $innerTable->addCell($innerColWidth, $page['inner']['cell'])->addText($value); // Уменьшаем ширину, чтобы создать отступ
            }
        }
    }
    public function get($portalContractId) //by id
    {
        $portalContract = PortalContract::find($portalContractId);

        try {

            if ($portalContract) {
                // $resultSmart = new SmartResource($rpa);
                return APIController::getSuccess(
                    ['portalcontract' => $portalContract]
                );
            } else {
                return APIController::getError(
                    'portalcontract was not found',
                    ['portalcontract' => $portalContract]
                );
            }
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                ['portalcontractId' => $portalContract]
            );
        }
    }

    public  function getAll()
    {

        // Создание нового Counter
        $portalcontracts = PortalContract::all();
        if ($portalcontracts) {

            return APIController::getSuccess(
                ['portalcontracts' => $portalcontracts]
            );
        }


        return APIController::getSuccess(
            ['portalcontracts' => []]
        );
    }

    public function getByPortal(Request $request)
    {
        $domain =  $request->domain;
        $portalcontracts = [];
        $resultContracts = [];
        // Создание нового Counter
        $portal = Portal::where('domain', $domain)->first();
        if ($portal) {
            $portalcontracts = $portal->contracts;
            if (!empty($portalcontracts)) {

                foreach ($portalcontracts as $portalcontract) {
                    $resultContract = new PortalContractResource($portalcontract);



                    if (!empty($portalcontract['contract'])) {


                        // if (empty($portalcontract['productName'])) {
                        //     $resultContract['productName'] = $portalcontract['contract']['productName'];
                        // }
                        // if (!empty($portalcontract['portal_measure'])) {

                        //     if (!empty($portalcontract['portal_measure']['bitrixId'])) {

                        //         $resultContract['bitrixMeasureId'] = (int)$portalcontract['portal_measure']['bitrixId'];
                        //     }
                        // }
                    }

                    // $fieldItem = BitrixfieldItem::find($portalcontract['bitrixfield_item_id']);
                    // $field = Bitrixfield::find($fieldItem['bitrixfield_id']);


                    // $portalcontract['code'] = $portalcontract['contract']['code'];
                    // $portalcontract['shortName'] = $portalcontract['contract']['code'];
                    // $portalcontract['number'] = $portalcontract['contract']['number'];


                    // $resultContract['fieldItem'] = $fieldItem;
                    // $resultContract['field'] = $field;
                    // $portalcontract['aprilName'] =  $portalcontract;
                    // $resultContract['bitrixName'] =  $fieldItem['title'];
                    // $portalcontract['discount'] = (int)$portalcontract['contract']['discount'];
                    // $portalcontract['prepayment'] = (int)$portalcontract['contract']['prepayment'];

                    // $resultContract['itemId'] =  $fieldItem['bitrixId'];

                    array_push($resultContracts, $resultContract);
                }




                return APIController::getSuccess(
                    ['contracts' => $resultContracts]
                );
            }
        }


        return APIController::getSuccess(
            ['contracts' => $portalcontracts, 'portal' => $portal]
        );
    }



    //utils
    protected function getClientRqForm($bxRq, $bxAdressesRq, $bxBankRq, $contractType)
    {
        $clientRole = 'Заказчик';

        switch ($contractType) {
            case 'abon':
            case 'key':
                $clientRole = 'Покупатель';
                break;
            case 'lic':
                $clientRole = 'Лицензиат';
                break;
            default:
                $clientRole = 'Заказчик';
                # code...
                break;
        }

        $registredadvalue = '';
        $primaryadvalue = '';
        if (!empty($bxAdressesRq) && is_array($bxAdressesRq)) {
            foreach ($bxAdressesRq as $bxAdressRq) {
                $isRegisrted = false;
                $isFizRegisrted = false;
                if ($bxAdressRq['TYPE_ID'] == 4) { // Адрес регистрации
                    $isRegisrted = true;

                    if (!empty($bxAdressRq['ADDRESS_1'])) {
                        $advalue = $this->getAddressString($bxAdressRq);
                        $registredadvalue = $advalue;

                        // foreach ($result['address'] as $resultAddress) {
                        //     if ($resultAddress['code'] == 'registredAdress' && $resultAddress['name'] ==  'Адрес прописки') {
                        //         $resultAddress['value'] = $advalue;
                        //     }
                        // }
                        // for ($i = 0; $i < count($result['address']); $i++) {
                        //     if ($result['address'][$i]['code'] === 'registredAdress'  && $result['address'][$i]['name'] ==  'Адрес прописки') {
                        //         $result['address'][$i]['value'] = $advalue;
                        //         array_push($result['rq'], $result['address'][$i]);
                        //     }
                        // }
                    }
                } else  if ($bxAdressRq['TYPE_ID'] == 6) {  // Юридический адрес
                    if (!empty($bxAdressRq['ADDRESS_1'])) {
                        $advalue = $this->getAddressString($bxAdressRq);
                        $registredadvalue = $advalue;
                        // foreach ($result['address'] as $resultAddress) {
                        //     if ($resultAddress['code'] == 'registredAdress') {
                        //         $resultAddress['value'] = $advalue;
                        //     }
                        // }

                        // for ($i = 0; $i < count($result['address']); $i++) {
                        //     if ($result['address'][$i]['code'] === 'registredAdress') {
                        //         $result['address'][$i]['value'] = $advalue;
                        //         array_push($result['rq'], $result['address'][$i]);
                        //     }
                        // }
                    }
                } else {
                    if (!empty($bxAdressRq['ADDRESS_1'])) {
                        $advalue = $this->getAddressString($bxAdressRq);
                        $primaryadvalue = $advalue;
                        // foreach ($result['address'] as $resultAddress) {
                        //     if ($resultAddress['code'] == 'primaryAdresss') {
                        //         $resultAddress['value'] = $advalue;
                        //     }
                        // }

                        // for ($i = 0; $i < count($result['address']); $i++) {
                        //     if ($result['address'][$i]['code'] === 'primaryAdresss') {
                        //         $result['address'][$i]['value'] = $advalue;
                        //         array_push($result['rq'], $result['address'][$i]);
                        //     }
                        // }
                    }
                }
            }
        }

        $result = [
            'rq' => [
                [
                    'type' => 'string',
                    'name' => 'ID',
                    'value' => '',
                    'isRequired' => false,
                    'code' => 'id',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 1,



                ],

                [
                    'type' => 'string',
                    'name' => 'Полное наименование организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'fullname',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1,



                ],
                // [
                //     'type' => 'string',
                //     'name' => 'Сокращенное наименование организации',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'shortname',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 2,


                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Роль клиента в договоре',
                //     'value' => $clientRole,
                //     'isRequired' => true,
                //     'code' => 'role',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => true,
                //     'order' => 3

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Должность руководителя организации',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'position',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'order' => 4
                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Должность руководителя организации (в лице)',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'position_case',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'order' => 6,

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'ФИО руководителя организации',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'director',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 5,

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'ФИО руководителя организации (в лице)',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'director_case',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 7

                // ],

                // [
                //     'type' => 'string',
                //     'name' => 'Действующий на основании',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'based',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 8

                // ],


                [
                    'type' => 'string',
                    'name' => 'ИНН',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'inn',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 9

                ],
                // [
                //     'type' => 'string',
                //     'name' => 'КПП',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'kpp',
                //     'includes' => ['org', 'org_state'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 10

                // ],

                // [
                //     'type' => 'string',
                //     'name' => 'ОГРН',
                //     'value' => '',
                //     'isRequired' => false,
                //     'code' => 'ogrn',
                //     'includes' => ['org', 'org_state'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 11

                // ],

                // [
                //     'type' => 'string',
                //     'name' => 'ОГРНИП',
                //     'value' => '',
                //     'isRequired' => false,
                //     'code' => 'ogrnip',
                //     'includes' => ['ip'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 12

                // ],

                // [
                //     'type' => 'string',
                //     'name' => 'ФИО главного бухгалтера организации',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'accountant',
                //     'includes' => ['org', 'org_state'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 13

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'ФИО ответственного за получение справочника',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'assigned',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 14

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Телефон ответственного за получение справочника',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'assignedPhone',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 15

                // ],

                [
                    'type' => 'string',
                    'name' => 'Телефон организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'phone',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 16

                ],
                [
                    'type' => 'string',
                    'name' => 'E-mail',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'email',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 17

                ],
                // [
                //     'type' => 'string',
                //     'name' => 'ФИО',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'personName',
                //     'includes' => ['fiz', 'advokat'],
                //     'group' => 'header',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 0

                // ],


                // [
                //     'type' => 'string',
                //     'name' => 'Вид Документа',
                //     'value' => 'Паспорт',
                //     'isRequired' => true,
                //     'code' => 'document',
                //     'includes' => ['fiz', 'advokat'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 1

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Серия Документа',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'docSer',
                //     'includes' => ['fiz', 'advokat'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 2

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Номер Документа',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'docNum',
                //     'includes' => ['fiz', 'advokat'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 3

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Дата Выдачи Документа',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'docDate',
                //     'includes' => ['fiz', 'advokat'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 4

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Документ выдан подразделением',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'docDate',
                //     'includes' => ['fiz', 'advokat'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 5

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Код подразделения',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'docDate',
                //     'includes' => ['fiz', 'advokat'],
                //     'group' => 'rq',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 6

                // ],

                [
                    'type' => 'text',
                    'name' => 'Юридический адрес',
                    'value' => $registredadvalue,
                    'isRequired' => false,
                    'code' => 'registredAdress',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0,
                    'all' => $bxAdressesRq

                ],

                [
                    'type' => 'text',
                    'name' => 'Адрес прописки',
                    'value' => $registredadvalue,
                    'isRequired' => false,
                    'code' => 'registredAdress',
                    'includes' => ['fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1,

                ],
                [
                    'type' => 'text',
                    'name' => 'Фактический адрес',
                    'value' =>  $primaryadvalue,
                    'isRequired' => true,
                    'code' => 'primaryAdresss',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2,

                ],
                [
                    'type' => 'text',
                    'name' => 'Прочие реквизиты',
                    'value' => '',
                    'isRequired' => false,
                    'code' => 'other',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 20


                ],


            ],
            'address' => [
                [
                    'type' => 'text',
                    'name' => 'Юридический адрес',
                    'value' => '',
                    'isRequired' => false,
                    'code' => 'registredAdress',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0,

                ],

                [
                    'type' => 'text',
                    'name' => 'Адрес прописки',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'registredAdress',
                    'includes' => ['fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1,

                ],
                [
                    'type' => 'text',
                    'name' => 'Фактический адрес',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'primaryAdresss',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2,

                ],
            ],
            'bank' => [
                [
                    'type' => 'string',
                    'name' => 'Наименование банка',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'bank',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0,
                ],
                [
                    'type' => 'string',
                    'name' => 'Адрес банка',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'bankAdress',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1,
                ],
                [
                    'type' => 'string',
                    'name' => 'БИК',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'bik',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2,
                ],
                [
                    'type' => 'string',
                    'name' => 'р/с',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'rs',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 3,
                ],
                [
                    'type' => 'string',
                    'name' => 'к/с',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'ks',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 4,
                ],

                [
                    'type' => 'text',
                    'name' => 'Прочие банковские реквизиты',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'bankOther',
                    'includes' => ['org', 'org_state', 'ip', 'advokat'],
                    'group' => 'bank',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 5,
                ],
            ]
        ];
        if (!empty($bxRq)) {

            foreach ($bxRq as $bxRqFieldName => $value) {
                switch ($bxRqFieldName) {
                    case 'ID': //Название реквизита. Обязательное поле.
                        for ($i = 0; $i < count($result['rq']); $i++) {

                            if ($result['rq'][$i]['code'] === 'id') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        };
                        break;

                    case 'NAME': //Название реквизита. Обязательное поле.

                        break;
                    case 'RQ_NAME': //Ф.И.О.  физлица ип

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'fullname' || $result['rq'][$i]['code'] === 'personName') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }

                        // foreach ($result['rq'] as $rq) {

                        //     if ($rq['code'] === 'fullname') {
                        //         $rq['value'] = $value;
                        //     }

                        //     if ($rq['code'] === 'personName') {
                        //         $rq['value'] = $value;
                        //     }
                        // }
                        break;
                    case 'RQ_COMPANY_NAME': //Сокращенное наименование организации.
                        // foreach ($result['rq'] as $rq) {
                        //     if ($rq['code'] === 'name') {
                        //         $rq['value'] = $value;
                        //     }
                        // }
                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'name') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;
                    case 'RQ_COMPANY_FULL_NAME':
                        // Log::channel('telegram')->info('ONLINE TEST', [$bxRqFieldName => $value]);

                        // foreach ($result['rq'] as $rq) {
                        //     if ($rq['code'] === 'fullname') {
                        //         $rq['value'] = $value;
                        //         Log::channel('telegram')->info('ONLINE TEST', ['$result rq' => $rq]);
                        //     }
                        // }
                        // Log::channel('telegram')->info('ONLINE TEST', ['$result rq' => $result['rq']]);
                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'fullname') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;
                    case 'RQ_DIRECTOR': //Ген. директор.

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'director') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;
                    case 'RQ_ACCOUNTANT': // Гл. бухгалтер.
                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'accountant') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }

                        break;
                    case 'RQ_EMAIL':

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'email') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }

                        break;
                    case 'RQ_PHONE':

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'phone') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }

                        break;


                        //fiz lic
                    case 'RQ_IDENT_DOC': //Вид документа.

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'document') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }

                        break;
                    case 'RQ_IDENT_DOC_SER': //Серия.

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'docSer') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;
                    case 'RQ_IDENT_DOC_NUM': // Номер.

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'docNum') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;
                    case 'RQ_IDENT_DOC_DATE': //Дата выдачи.

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'docDate') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;

                    case 'RQ_IDENT_DOC_ISSUED_BY': //Кем выдан.

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'docIssuedBy') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;

                    case 'RQ_IDENT_DOC_DEP_CODE': //Код подразделения

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'docDepCode') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;


                    case 'RQ_INN':

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'inn') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;

                    case 'RQ_KPP':

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'kpp') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;

                    case 'RQ_OGRN':

                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'ogrn') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;


                    case 'RQ_OGRNIP':


                        for ($i = 0; $i < count($result['rq']); $i++) {
                            if ($result['rq'][$i]['code'] === 'ogrnip') {
                                $result['rq'][$i]['value'] = $value;
                            }
                        }
                        break;


                    case 'RQ_OKPO':
                        # code...
                        break;

                    case 'RQ_OKTMO':
                        # code...
                        break;

                    case 'RQ_OKVED':
                        # code...
                        break;

                    default:

                        break;
                }
            }
        }

        if (!empty($bxBankRq)) {

            foreach ($bxBankRq as $bxBankFieldName => $value) {
                switch ($bxBankFieldName) {
                    case 'NAME': //Название реквизита. Обязательное поле.
                        # code...
                        break;
                    case 'RQ_BANK_NAME': //Ф.И.О.  физлица ип
                        // foreach ($result['bank'] as $bankRQ) {
                        //     if ($bankRQ['code'] === 'bank') {
                        //         $bankRQ['value'] = $value;
                        //     }
                        // }
                        for ($i = 0; $i < count($result['bank']); $i++) {
                            if ($result['bank'][$i]['code'] === 'bank') {
                                $result['bank'][$i]['value'] = $value;
                            }
                        }

                        break;
                    case 'RQ_BANK_ADDR': //Сокращенное наименование организации.
                        // foreach ($result['bank'] as $bankRQ) {
                        //     if ($bankRQ['code'] === 'bankAdress') {
                        //         $bankRQ['value'] = $value;
                        //     }
                        // }
                        for ($i = 0; $i < count($result['bank']); $i++) {
                            if ($result['bank'][$i]['code'] === 'bankAdress') {
                                $result['bank'][$i]['value'] = $value;
                            }
                        }


                        break;
                    case 'RQ_BIK':
                        // foreach ($result['bank'] as $bankRQ) {
                        //     if ($bankRQ['code'] === 'bik') {
                        //         $bankRQ['value'] = $value;
                        //     }
                        // }
                        for ($i = 0; $i < count($result['bank']); $i++) {
                            if ($result['bank'][$i]['code'] === 'bik') {
                                $result['bank'][$i]['value'] = $value;
                            }
                        }
                        break;
                    case 'RQ_COR_ACC_NUM': //Ген. директор.
                        // foreach ($result['bank'] as $bankRQ) {
                        //     if ($bankRQ['code'] === 'ks') {
                        //         $bankRQ['value'] = $value;
                        //     }
                        // }
                        for ($i = 0; $i < count($result['bank']); $i++) {
                            if ($result['bank'][$i]['code'] === 'ks') {
                                $result['bank'][$i]['value'] = $value;
                            }
                        }
                        break;
                    case 'RQ_IBAN': // Гл. бухгалтер.
                        # code...
                        break;
                    case 'RQ_ACC_NUM':
                        // foreach ($result['bank'] as $bankRQ) {
                        //     if ($bankRQ['code'] === 'rs') {
                        //         $bankRQ['value'] = $value;
                        //     }
                        // }
                        for ($i = 0; $i < count($result['bank']); $i++) {
                            if ($result['bank'][$i]['code'] === 'rs') {
                                $result['bank'][$i]['value'] = $value;
                            }
                        }
                        break;
                    case 'COMMENTS':
                        // foreach ($result['bank'] as $bankRQ) {
                        //     if ($bankRQ['code'] === 'bankOther') {
                        //         $bankRQ['value'] = $value;
                        //     }
                        // }
                        for ($i = 0; $i < count($result['bank']); $i++) {
                            if ($result['bank'][$i]['code'] === 'bankOther') {
                                $result['bank'][$i]['value'] = $value;
                            }
                        }
                        break;
                    default:
                        // foreach ($result['bank'] as $bankRQ) {
                        //     if ($bankRQ['code'] === 'bankOther') {
                        //         $rq['value'] = $rq['value'] . ' ' . $value;
                        //     }
                        // }
                }
            }
        }
        return ['rq' => $result['rq'], 'bank' => $result['bank'], 'address' => $result['address']];
    }
    protected function getContractGeneralForm($arows, $contractQuantity)
    {


        $prepaymentQuantity = $contractQuantity;
        $prepaymentSum = 0;
        $monthSum = 0;
        $contractsum = 0;

        foreach ($arows as $row) {
            if (!empty($row['price'])) {
                $monthSum = (float)$monthSum  + (float)$row['price']['month'];
                $prepaymentSum = (float)$prepaymentSum  + (float)$row['price']['sum'];
                $prepaymentQuantity = (float)$contractQuantity * (float)$row['price']['quantity'];
            }
        }
        $contractsum = $monthSum * 12;


        return [
            [
                'type' => 'date',
                'name' => 'Договор от',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_create_date',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 0,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],



            ],
            [
                'type' => 'date',
                'name' => 'Действие договора с',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_start',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 1,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Действие договора по',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_finish',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 2,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Предоплата с',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_start',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 5,
                'includes' => ['org', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Предоплата по',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_finish',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 6,
                'includes' => ['org',  'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Внести предоплату не позднее',
                'value' => '',
                'isRequired' => true,
                'code' => 'prepayment_deadline',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 7,
                'includes' => ['org',  'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Период в подарок с',
                'value' => '',
                'isRequired' => true,
                'code' => 'present_start',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 8,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Период в подарок по',
                'value' => '',
                'isRequired' => true,
                'code' => 'present_finish',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 9,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'string',
                'name' => 'Сумма по договору',
                'value' => $contractsum,
                'isRequired' => true,
                'code' => 'period_sum',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => true,
                'order' => 10,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'string',
                'name' => 'Сумма предоплаты/Лицензии',
                'value' =>  $prepaymentSum,
                'isRequired' => true,
                'code' => 'prepayment_sum',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => true,
                'order' => 5,
                'includes' => ['org',  'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'string',
                'name' => 'Сумма в месяц',
                'value' => $monthSum,
                'isRequired' => true,
                'code' => 'month_sum',
                'group' => 'contract',
                'isActive' => true,
                'isDisable' => false,
                'order' => 6,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service']


            ],
        ];
    }


    protected function getSpecification(
        $currentComplect,
        $products, //garant product or products from general garant only
        $consaltingProduct,
        $lt,
        $starProduct,
        $contractType, //service | product
        // SERVICE='service',
        // ABON='abon',
        // LIC='lic',
        // KEY='key',
        $contract,

        $arows,
        $contractQuantity,
        $documentInfoblocks,
        $contractProductName,
        $total
    ) {

        $productType = [
            'abs' => false,
            'complectName' => 'Юрист',
            'complectNumber' => 2,
            'complectType' => 'prof',
            'contract' => [
                'id' => 3,
                'contract' => ['details' => 'Детали контракта'],
                'code' => 'internet',
                'shortName' => 'internet',
                'number' => 0
            ],
            'contractCoefficient' => 1,
            'contractConsaltingComment' => '',
            'contractConsaltingProp' => '',
            'contractName' => 'Internet',
            'contractNumber' => 0,
            'contractShortName' => 'internet',
            'contractSupplyName' => 'В электронном виде по каналам связи посредством телекоммуникационной сети Интернет Многопользовательская Интернет-версия 1',
            'contractSupplyProp1' => '',
            'contractSupplyProp2' => '',
            'contractSupplyPropComment' => 'Для работы с комплектом Справочника в электронном виде по каналам связи посредством телекоммуникационной сети Исполнитель предоставляет Заказчику в электронном виде на адрес электронной почты, указанный Заказчиком в настоящем Приложении, информацию об административной учетной записи, с помощью которой Заказчиком заводятся логины и пароли Пользователей.',
            'contractSupplyPropEmail' => 'Адрес электронной почты Заказчика, на который Исполнитель присылает информацию об административной учетной записи.',
            'contractSupplyPropLoginsQuantity' => 'Неограниченно с возможностью одновременной работы одного Пользователя',
            'contractSupplyPropSuppliesQuantity' => 1,
            'discount' => 1,
            'measureCode' => 'month',
            'measureFullName' => 'Месяц',
            'measureId' => 11,
            'measureName' => 'mec.',
            'measureNumber' => 2,
            'mskPrice' => false,
            'name' => 'Гарант-Юрист',
            'number' => 851,
            'prepayment' => 1,
            'price' => 8008,
            'productId' => null,
            'quantityForKp' => 'Интернет версия на 1 одновременный доступ к системе',
            'regionsPrice' => false,
            'supply' => [
                'contractPropSuppliesQuantity' => 1,
                'lcontractProp2' => '',
                'lcontractName' => 'Многопользовательская Интернет-версия 1',
                'lcontractPropEmail' => 'Адрес электронной почты Лицензиата, на который Лицензиар присылает информацию.',
                'type' => 'internet'
            ],
            'supplyName' => 'Интернет 1 ОД',
            'supplyNumber' => 1,
            'supplyType' => 'internet',
            'totalPrice' => 8008,
            'type' => 'garant',
            'withAbs' => false,
            'withConsalting' => false,
            'withPrice' => false
        ];

        $prepaymentQuantity = $contractQuantity;
        $prepaymentSum = 0;
        $monthSum = 0;
        $contractsum = 0;
        $product = $products[0];
        $contractSupplyName = $product['contractSupplyName'];
        $contractSupplyPropComment = $product['contractSupplyPropComment'];
        $contractSupplyPropEmail = $product['contractSupplyPropEmail']; //коммент к email для интернет или для носителя флэш
        $contractCoefficient = $product['contractCoefficient'];
        $generalProductQuantity = 1;
        $contractSupplyPropLoginsQuantity = $product['contractSupplyPropLoginsQuantity'];
        $contractSupplyProp1  = $product['contractSupplyProp1']; //носители
        $contractSupplyProp2  = $product['contractSupplyProp2']; //способ доставки носителей


        $consalting =  $contractConsaltingComment = $product['contractConsaltingComment'];
        $supplyType = $product['supply']['type']; //internet | proxima
        foreach ($arows as $row) {
            if (!empty($row['price'])) {
                $monthSum = (float)$monthSum  + (float)$row['price']['month'];
                $prepaymentSum = (float)$prepaymentSum  + (float)$row['price']['sum'];
                $prepaymentQuantity = (float)$contractQuantity * (float)$row['price']['quantity'];
                $generalProductQuantity = (int)$row['price']['quantity'];
            }
        }
        $contractsum = $monthSum * 12;
        $products_names = $contractProductName . "\n" . 'Гарант-' . $product['complectName'];

        // if(!empty($total)){
        //     if(!empty($total['name'])){
        $products_names = $total['name'];
        //     }
        // }

        $consalting = '';
        $consaltingName = 'Горячая Линия';
        $consaltingcomment = '';
        if (!empty($consaltingProduct)) {
            $consalting = $consaltingProduct['contractConsaltingProp'];
            $consaltingcomment = $consaltingProduct['contractConsaltingComment'];
            if (!empty($consaltingProduct['name'])) {
                $consaltingName = $consaltingProduct['name'];
            }
        }
        $ltProduct = $lt['product'];

        $freeLtPack = '';
        $freeLtBlocks = '';
        $ltPack = '';
        $ltBlocks = '';



        if (!empty($currentComplect['lt'])) {
            $packWeight = count($currentComplect['lt']);
            if (!empty($lt['packages'])) {
                if (!empty($lt['packages'][$packWeight])) {
                    $pack = $lt['packages'][$packWeight];
                    // if (!empty($pack)) {
                    $freeLtPack =  $pack['fullName'];
                    // }

                    // foreach ($currentComplect['lt'] as $ltIndex) {
                    //     $freeLtBlocks = $freeLtBlocks . ' ' . $lt['value'][$ltIndex]['name'];
                    // }
                    if (!empty($lt['value'])) {

                        foreach ($lt['value'] as $ltservice) {
                            if (!empty($ltservice['name'])) {

                                if (in_array($ltservice['number'], $currentComplect['lt'])) {
                                    $freeLtBlocks .=  ' ' . $ltservice['name'] . "\n";
                                }
                            }
                        }
                    }
                }
            }
        }
        if (!empty($currentComplect['ltInPacket'])) {
            $packWeight = count($currentComplect['ltInPacket']);
            if (!empty($lt['packages'])) {
                if (!empty($lt['packages'][$packWeight])) {
                    $pack = $lt['packages'][$packWeight];
                    // if (!empty($pack)) {
                    $ltPack =  $pack['fullName'];
                    // }

                    foreach ($lt['value'] as $ltservice) {
                        if (!empty($ltservice['name'])) {
                            if (in_array($ltservice['number'], $currentComplect['ltInPacket'])) {
                                $ltBlocks .=  '' . $ltservice['name'] . "\n";
                            }
                        }
                    }

                    // foreach ($currentComplect['ltInPacket'] as $ltIndex) {
                    //     $freeLtBlocks .= ' ' . $lt['value'][$ltIndex]['name'];
                    // }
                }
            }
        }

        $iblocks = $this->getContractIBlocks($documentInfoblocks);

        if (!empty($contract)) {
            if (!empty($contract['code'])) {
                // INTERNET = 'internet',
                // PROXIMA = 'proxima',
                // ABON6 = 'abonHalf',
                // ABON12 = 'abonYear',
                // ABON24 = 'abonTwoYears',
                // LIC = 'lic',
                // LIC6 = 'licHalf',
                // LIC12 = 'licYear',
                // LIC24 = 'licTwoYears',

                if ($contractType === 'service') {
                    if ($contract['code'] == 'internet') {
                    } else  if ($contract['code'] == 'proxima') {
                    }
                } else   if ($contractType === 'abon') {
                } else   if ($contractType === 'lic') {
                    if ($supplyType == 'internet') {
                    } else   if ($supplyType == 'proxima') {
                    }
                } else   if ($contractType === 'key') {
                }
            }

            return [
                [
                    'type' => 'string',
                    'name' => 'Email для интернет-версии',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_email',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'supplies' => ['internet'],

                ],
                [
                    'type' => 'string',
                    'name' => 'ФИО ответственного за получение справочника',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'assigned',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0,
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'supplies' => ['internet', 'proxima'],

                ],
                [
                    'type' => 'string',
                    'name' => 'Телефон ответственного за получение справочника',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'assignedPhone',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1,
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'supplies' => ['internet', 'proxima'],


                ],
                [
                    'type' => 'text',
                    'name' => 'Наименование ',
                    'value' => $products_names,
                    'isRequired' => false,
                    'code' => 'complect_name',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 0,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                // [
                //     'type' => 'string',
                //     'name' => 'Наименование ',
                //     'value' => $products_names,
                //     'isRequired' => true,
                //     'code' => 'complect_name',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => true,
                //     'order' => 0,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'supplies' => ['internet', 'proxima'],
                //     'contractType' => ['service', 'lic', 'abon', 'key']


                // ],
                [
                    'type' => 'string',
                    'name' => 'Вид поставки',
                    'value' => $product['quantityForKp'],
                    'isRequired' => false,
                    'code' => 'contract_spec_products_names_comment',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 1,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                // [
                //     'type' => 'text',
                //     'name' => 'Вид Размещения',
                //     'value' => $contractSupplyName,
                //     'isRequired' => true,
                //     'code' => 'specification_supply',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => true,
                //     'order' => 1.5,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'supplies' => ['internet', 'proxima'],
                //     'contractType' => ['service', 'lic', 'abon', 'key']


                // ],
                [
                    'type' => 'string',
                    'name' => 'ПК/ГЛ',
                    'value' => $consaltingName,
                    'isRequired' => false,
                    'code' => 'specification_pk',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 2,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'string',
                    'name' => 'ПК/ГЛ договор',
                    'value' => $consalting,
                    'isRequired' => false,
                    'code' => 'specification_pk_comment1',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => true,
                    'order' => 2,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                // [
                //     'type' => 'text',
                //     'name' => 'Комментарий к ПК/ГЛ',
                //     'value' => $consaltingcomment,
                //     'isRequired' => true,
                //     'code' => 'specification_pk_comment',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => true,
                //     'order' => 3,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'supplies' => ['internet', 'proxima'],
                //     'contractType' => ['service', 'lic', 'abon', 'key']


                // ],
                [
                    'type' => 'text',
                    'name' => 'Информационные блоки',
                    'value' => $iblocks['allIBlocks'],
                    'isRequired' => false,
                    'code' => 'specification_ibig',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 4,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true



                ],
                [
                    'type' => 'text',
                    'name' => 'Энциклопедии Решений',
                    'value' =>  $iblocks['ers'],
                    'isRequired' => false,
                    'code' => 'specification_ers',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 5,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'text',
                    'name' => 'Пакеты Энциклопедий Решений',
                    'value' =>  $iblocks['erPackets'],
                    'isRequired' => false,
                    'code' => 'specification_ers_packets',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 5,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'text',
                    'name' => 'Состав Пакетов Энциклопедий Решений',
                    'value' =>  $iblocks['ersInPacket'],
                    'isRequired' => false,
                    'code' => 'specification_ers_in_packets',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 5,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],
                // [
                //     'type' => 'text',
                //     'name' => 'Малые информационные блоки',
                //     'value' =>  $iblocks['smallIBlocks'],
                //     'isRequired' => true,
                //     'code' => 'specification_ismall',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 5,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'supplies' => ['internet', 'proxima'],
                //     'contractType' => ['service', 'lic', 'abon', 'key']


                // ],
                [
                    'type' => 'text',
                    'name' => 'Бесплатные информационные блоки',
                    'value' => $iblocks['freeIBlocks'],
                    'isRequired' => false,
                    'code' => 'specification_ifree',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 6,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'string',
                    'name' => 'Legal Tech в комплекте (бесплатно)',
                    'value' => $freeLtPack,
                    'isRequired' => false,
                    'code' => 'specification_lt_free',
                    'group' => 'specification',
                    'isActive' => !empty($freeLtPack),
                    'isDisable' => true,
                    'order' => 7,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'text',
                    'name' => 'Состав Бесплатных Legal Tech',
                    'value' => $freeLtBlocks,
                    'isRequired' => false,
                    'code' => 'specification_lt_free_services',
                    'group' => 'specification',
                    'isActive' => !empty($freeLtBlocks),
                    'isDisable' => true,
                    'order' => 8,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'string',
                    'name' => 'Платный Пакет Legal Tech',
                    'value' => $ltPack,
                    'isRequired' => false,
                    'code' => 'specification_lt_packet',
                    'group' => 'specification',
                    'isActive' => !empty($ltPack),
                    'isDisable' => true,
                    'order' => 9,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'text',
                    'name' => 'Состав Платного Legal Tech',
                    'value' => $ltBlocks,
                    'isRequired' => false,
                    'code' => 'specification_lt_services',
                    'group' => 'specification',
                    'isActive' => !empty($ltBlocks),
                    'isDisable' => true,
                    'order' => 10,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'text',
                    'name' => 'Другие сервисы',
                    'value' =>  $iblocks['starLtBlocks'],
                    'isRequired' => false,
                    'code' => 'specification_services',
                    'group' => 'specification',
                    'isActive' => !empty($iblocks['starLtBlocks']),
                    'isDisable' => true,
                    'order' => 11,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],

                // [
                //     'type' => 'text',
                //     'name' => 'Примечание Вид Размещения',
                //     'value' => $product['contractSupplyPropComment'],
                //     'isRequired' => true,
                //     'code' => 'specification_supply_comment',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 13,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'supplies' => ['internet', 'proxima'],
                //     'contractType' => ['service', 'lic', 'abon', 'key']


                // ],

                // [
                //     'type' => 'text',
                //     'name' => 'Носители, используемые при предоставлении услуг',
                //     'value' => $contractSupplyProp1,
                //     'isRequired' => true,
                //     'code' => 'specification_distributive',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 14,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'supplies' => ['proxima'],
                //     'contractType' => ['service', 'lic',  'key']


                // ],
                // [
                //     'type' => 'text',
                //     'name' => 'Примечание Носители',
                //     'value' => $contractSupplyPropEmail,
                //     'isRequired' => true,
                //     'code' => 'specification_distributive_comment',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 15,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'supplies' => ['proxima'],
                //     'contractType' => ['service', 'lic',  'key']


                // ],
                // [
                //     'type' => 'text',
                //     'name' => 'Носители дистрибутивов предоставляются следующим способом',
                //     'value' => $contractSupplyProp2,
                //     'isRequired' => true,
                //     'code' => 'specification_dway',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 16,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'supplies' => ['proxima'],
                //     'contractType' => ['service', 'lic',  'key']


                // ],
                // [
                //     'type' => 'text',
                //     'name' => 'Примечание к способу',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'specification_dway_comment',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 17,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'supplies' => ['proxima'],
                //     'contractType' => ['service', 'lic',  'key']


                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Периодичность предоставления услуг',
                //     'value' => '1 неделя',
                //     'isRequired' => true,
                //     'code' => 'specification_service_period',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 18,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'contractType' => ['service', 'lic'],
                //     'supplies' => ['proxima'],

                // ],
                [
                    'type' => 'text',
                    'name' => 'Количество логинов и паролей ',
                    'value' => $contractSupplyPropLoginsQuantity,
                    'isRequired' => false,
                    'code' => 'specification_supply_quantity',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 19,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'supplies' => ['internet'],


                ],
                [
                    'type' => 'string',
                    'name' => 'Длительность работы ключа',
                    'value' => $generalProductQuantity *  $contractCoefficient . ' мес.',
                    'isRequired' => false,
                    'code' => 'specification_key_period',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 20,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['key'],
                    'supplies' => ['internet', 'proxima'],


                ],

                // [
                //     'type' => 'text',
                //     'name' => 'Email прмечание',
                //     'value' => $contractSupplyPropEmail,
                //     'isRequired' => true,
                //     'code' => 'specification_email_comment',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 22,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'contractType' => ['service', 'lic', 'abon', 'key'],
                //     'supplies' => ['internet'],


                // ],
                [
                    'type' => 'string',
                    'name' => 'Срок действия абонемента',
                    'value' => $generalProductQuantity *  $contractCoefficient,
                    'isRequired' => false,
                    'code' => 'abon_long',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 23,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['abon'],
                    'supplies' => ['internet'],


                ],


                [
                    'type' => 'text',
                    'name' => 'Срок действия лицензии, количество лицензий',
                    'value' => 'Количество лицензий: '
                        .  (min($generalProductQuantity, $contractCoefficient))
                        . "\n" . 'Срок действия лицензии: '
                        .  (max($generalProductQuantity, $contractCoefficient))
                        . ' (месяц) ',

                    'isRequired' => false,
                    'code' => 'lic_long',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 23,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['lic',],
                    'supplies' => ['internet', 'proxima'],


                ],




            ];
        }
    }


    protected function getSupplyReportData()
    {



        return [
            [
                'type' => 'date',
                'name' => 'Дата продажи',
                'value' => '',
                'isRequired' => true,
                'code' => 'sale_date',

                'isActive' => true,
                'isDisable' => false,
                'order' => 0,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'group' => 'supply',
                'component' => 'base_one'


            ],
            // [
            //     'type' => 'string',
            //     'name' => 'Комментарий',
            //     'value' => '',
            //     'isRequired' => true,
            //     'code' => 'sale_comment',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 1,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key']


            // ],
            [
                'type' => 'select',
                'name' => 'Передана в ОРК',

                'value' =>      [
                    'id' => 1,
                    'code' => 'no',
                    'name' => 'Нет',
                    'title' => 'Нет'
                ],


                'items' => [
                    [
                        'id' => 0,
                        'code' => 'yes',
                        'name' => 'Да',
                        'title' => 'Да'
                    ],
                    [
                        'id' => 1,
                        'code' => 'no',
                        'name' => 'Нет',
                        'title' => 'Нет'
                    ],

                ],


                'isRequired' => true,
                'code' => 'in_ork',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 2,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                // 'component' => 'base_one'


            ],
            [
                'type' => 'date',
                'name' => 'Клиент ждет звонка от менеджера ОРК',
                'value' => '',
                'isRequired' => true,
                'code' => 'client_call_date',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 2,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'base_one'



            ],
            [
                'type' => 'select',
                'name' => 'Занесена в АРМ',

                'value' =>  [
                    'id' => 1,
                    'code' => 'no',
                    'name' => 'Нет',
                    'title' => 'Нет'
                ],


                'items' => [
                    [
                        'id' => 0,
                        'code' => 'yes',
                        'name' => 'Да',
                        'title' => 'Да'
                    ],
                    [
                        'id' => 1,
                        'code' => 'no',
                        'name' => 'Нет',
                        'title' => 'Нет'
                    ],

                ],
                'isRequired' => true,
                'code' => 'in_arm',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 3,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                // 'component' => 'base'


            ],
            // [
            //     'type' => 'select',
            //     'name' => 'Что известно',
            //     'value' => [
            //         'id' => 1,
            //         'code' => 'success',
            //         'name' => 'Обслуживаются',
            //         'title' => 'Обслуживаются'
            //     ],
            //     'isRequired' => true,
            //     'code' => 'supply_information',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 4,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],
            //     'items' => [
            //         [
            //             'id' => 0,
            //             'code' => 'fail',
            //             'name' => 'Отказались',
            //             'title' => 'Отказались'
            //         ],
            //         [
            //             'id' => 1,
            //             'code' => 'success',
            //             'name' => 'Обслуживаются',
            //             'title' => 'Обслуживаются'
            //         ],


            //     ],
            //     'component' => 'client'


            // ],
            [
                'type' => 'select',
                'name' => 'Особенности оплаты клиентом счетов',
                'value' => [
                    'id' => 1,
                    'code' => 'commers',
                    'name' => 'Коммерческие',
                    'title' => 'Коммерческие'
                ],
                'isRequired' => true,
                'code' => 'invoice_pay_type',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 5,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'items' => [
                    [
                        'id' => 0,
                        'code' => 'budget',
                        'name' => 'Бюджетники',
                        'title' => 'Бюджетники'
                    ],
                    [
                        'id' => 1,
                        'code' => 'commers',
                        'name' => 'Коммерческие',
                        'title' => 'Коммерческие'
                    ],


                ],
                'component' => 'client'


            ],
            [
                'type' => 'select',
                'name' => 'Как у них с финансами',

                'value' =>   [
                    'id' => 0,
                    'code' => 'small',
                    'name' => 'Мелкий',
                    'title' => 'Мелкий'
                ],


                'items' => [
                    [
                        'id' => 0,
                        'code' => 'small',
                        'name' => 'Мелкий',
                        'title' => 'Мелкий'
                    ],
                    [
                        'id' => 1,
                        'code' => 'medium',
                        'name' => 'Средний',
                        'title' => 'Средний'
                    ],
                    [
                        'id' => 2,
                        'code' => 'big',
                        'name' => 'Крупный',
                        'title' => 'Крупный'
                    ],

                ],
                'isRequired' => true,
                'code' => 'finance',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 4,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'client'


            ],
            // [
            //     'type' => 'string',
            //     'name' => 'Менеджер отдела продаж',
            //     'value' => '',
            //     'isRequired' => true,
            //     'code' => 'manager',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 4,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],


            // ],
            // [
            //     'type' => 'string',
            //     'name' => 'Менеджер отдела ТМЦ',
            //     'value' => '',
            //     'isRequired' => true,
            //     'code' => 'tmc',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 4,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],


            // ],
            [
                'type' => 'select',
                'name' => 'Создавался ли Договор',
                'value' =>  [
                    'id' => 1,
                    'code' => 'no',
                    'name' => 'Нет',
                    'title' => 'Нет'
                ],


                'items' => [
                    [
                        'id' => 0,
                        'code' => 'yes',
                        'name' => 'Да',
                        'title' => 'Да'
                    ],
                    [
                        'id' => 1,
                        'code' => 'no',
                        'name' => 'Нет',
                        'title' => 'Нет'
                    ],

                ],
                'isRequired' => true,
                'code' => 'is_contract_done',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 6,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'contract'



            ],
            [
                'type' => 'string',
                'name' => 'Дата и номер',
                'value' => '',
                'isRequired' => false,
                'code' => 'contract_number',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 7,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'contract'


            ],
            [
                'type' => 'select',
                'name' => 'Судьба договора',
                'value' =>     [
                    'id' => 0,
                    'code' => 'in_progress',
                    'name' => 'На подписи у клиента',
                    'title' => 'На подписи у клиента'
                ],

                'items' => [
                    [
                        'id' => 0,
                        'code' => 'in_progress',
                        'name' => 'На подписи у клиента',
                        'title' => 'На подписи у клиента'
                    ],
                    [
                        'id' => 1,
                        'code' => 'done',
                        'name' => 'Подписан',
                        'title' => 'Подписан'
                    ],
                    [
                        'id' => 2,
                        'code' => 'edo',
                        'name' => 'ЭДОм',
                        'title' => 'ЭДОм'
                    ],

                ],
                'isRequired' => false,
                'code' => 'contract_result',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 8,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'contract'


            ],
            [
                'type' => 'file',
                'name' => 'Текyщий договор',
                'value' => '',
                'isRequired' => false,
                'code' => 'current_contract',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 7,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'contract'


            ],

            [
                'type' => 'select',
                'name' => 'Создавался ли Счет',
                'type' => 'select',
                'value' =>  [
                    'id' => 1,
                    'code' => 'no',
                    'name' => 'Нет',
                    'title' => 'Нет'
                ],


                'items' => [
                    [
                        'id' => 0,
                        'code' => 'yes',
                        'name' => 'Да',
                        'title' => 'Да'
                    ],
                    [
                        'id' => 1,
                        'code' => 'no',
                        'name' => 'Нет',
                        'title' => 'Нет'
                    ],

                ],
                'isRequired' => true,
                'code' => 'is_invoice_done',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 9,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'invoice'
                // 'component' => 'contract'



            ],
            [
                'type' => 'string',
                'name' => 'Дата и номер',
                'value' => '',
                'isRequired' => false,
                'code' => 'invoice_number',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 10,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'invoice'
                // 'component' => 'contract'



            ],
            [
                'type' => 'select',
                'name' => 'Его судьба',
                'value' =>   [
                    'id' => 0,
                    'code' => 'done',
                    'name' => 'Оплачен',
                    'title' => 'Оплачен'
                ],


                'items' => [
                    [
                        'id' => 0,
                        'code' => 'done',
                        'name' => 'Оплачен',
                        'title' => 'Оплачен'
                    ],
                    [
                        'id' => 1,
                        'code' => 'in_progress',
                        'name' => 'На оплате',
                        'title' => 'На оплате'
                    ],

                ],
                'isRequired' => false,
                'code' => 'invoice_result',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 11,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'invoice'
                // 'component' => 'contract'


            ],

            [
                'type' => 'file',
                'name' => 'Текущий счет',
                'value' => '',
                'isRequired' => false,
                'code' => 'current_invoice',
                'group' => 'supply',
                'isActive' => false,
                'isDisable' => false,
                'order' => 10,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'component' => 'invoice'
                // 'component' => 'contract'



            ],
            // [
            //     'type' => 'date',
            //     'name' => 'Дата оплаты Счета',
            //     'value' =>  '',
            //     'isRequired' => true,
            //     'code' => 'invoice_pay_date',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 12,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],
            //     'component' => 'invoice'



            // ],

            // [
            //     'type' => 'string',
            //     'name' => 'Компания в битрикс',
            //     'value' => '',
            //     'isRequired' => true,
            //     'code' => 'bitrix_company',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 13,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],


            // ],

            // [
            //     'type' => 'string',
            //     'name' => 'Сделка в битрикс',
            //     'value' => '',
            //     'isRequired' => true,
            //     'code' => 'bitrix_deal',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 14,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],


            // ],
            // [
            //     'type' => 'text',
            //     'name' => 'Источник',
            //     'value' => "",
            //     'isRequired' => true,
            //     'code' => 'source',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 15,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],


            // ],
            // [
            //     'type' => 'select',
            //     'name' => 'Регион',
            //     'value' =>  [
            //         'id' => 0,
            //         'code' => 'org',
            //         'name' => 'Организация Коммерческая',
            //         'title' => 'Организация Коммерческая'
            //     ],
            //     'isRequired' => true,

            //     'items' => [
            //         [
            //             'id' => 0,
            //             'code' => 'ro',
            //             'name' => 'РО',
            //             'title' => 'РО'
            //         ],
            //         [
            //             'id' => 1,
            //             'code' => 'ko',
            //             'name' => 'KО',
            //             'title' => 'KО'
            //         ],
            //         [
            //             'id' => 2,
            //             'code' => 'sk',
            //             'name' => 'CK',
            //             'title' => 'СК'
            //         ],

            //         [
            //             'id' => 3,
            //             'code' => 'lo',
            //             'name' => 'ЛО',
            //             'title' => 'ЛО'
            //         ],
            //         // [
            //         //     'id' => 3,
            //         //     'code' => 'advokat',
            //         //     'name' => 'Адвокат',
            //         //     'title' => 'Адвокат'
            //         // ],
            //         [
            //             'id' => 4,
            //             'code' => 'fiz',
            //             'name' => 'СПБ',
            //             'title' => 'СПБ'
            //         ],

            //     ],
            //     'code' => 'company_type',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 16,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],


            // ],

            [
                'type' => 'text',
                'name' => 'Описание ситуации, примечания, дополнительные сведения',
                'value' => '',
                'isRequired' => true,
                'code' => 'situation_comments',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 14,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],

            ],
            // [
            //     'type' => 'text',
            //     'name' => 'Контактные лица',
            //     'value' => '',
            //     'isRequired' => true,
            //     'code' => 'company_contacts',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 19,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],


            // ],
            // [
            //     'type' => 'string',
            //     'name' => 'Наличие конкурентов',
            //     'value' =>  '',
            //     'isRequired' => true,
            //     'code' => 'concurents',
            //     'group' => 'supply',
            //     'isActive' => true,
            //     'isDisable' => false,
            //     'order' => 20,
            //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
            //     'supplies' => ['internet', 'proxima'],
            //     'contractType' => ['service', 'lic', 'abon', 'key'],


            // ],



        ];
    }



    protected function getContractIBlocks($infoblocks)
    {
        $bigIBlocks = '';
        $smallIBlocks = '';
        $freeIBlocks = '';
        $packIBlocks = '';
        $starLtBlocks = '';
        $erPackets = '';
        $ersInPacket = '';
        $ers = '';
        $allIBlocks = '';

        foreach ($infoblocks as $group) {
            $isFree = false;
            $isLegal = false;
            $isStar = false;
            $isConsalting = false;
            $isERPack = false;
            $isER = false;
            if ($group['groupsName'] == 'Без дополнительной оплаты') {
                $isFree = true;
            }
            if ($group['groupsName'] == 'Legal Tech') {
                $isLegal = true;
            }
            if ($group['groupsName'] == 'Пакет Энциклопедий решений') {
                $isERPack = true;
            }
            if ($group['groupsName'] == 'Энциклопедии решений') {
                $isER = true;
            }
            if ($group['groupsName'] == 'Дополнительные программные продукты') {
                $isStar = true;
            }
            if ($group['groupsName'] == 'Правовая Поддержка') {
                $isConsalting = true;
            }

            foreach ($group['value'] as $iblock) {
                $value = $iblock['name'];
                if (!empty($iblock['title'])) {
                    $value = $iblock['title'];
                }

                if (
                    !$isFree &&
                    !$isLegal &&
                    !$isStar &&
                    !$isConsalting &&
                    !$isERPack &&
                    !$isER
                ) {



                    if ($iblock['weight'] == 0.5) {
                        $allIBlocks .=  '' . $value . "\n";
                        $smallIBlocks .=  '' . $value . "\n";
                    } else if ($iblock['weight'] >= 1) {
                        $allIBlocks .=  '' . $value . "\n";
                        $bigIBlocks .=  '' . $value . "\n";
                    }
                } else if ($isFree) {
                    $freeIBlocks .=  '' . $value . "\n";
                    // array_push($freeIBlocks, $value);
                } else if ($isStar) {
                    $starLtBlocks .=  '' . $value . "\n";
                    // array_push($starLtBlocks, $value);
                } else if (!empty($isER)) {
                    if ($iblock['weight'] == 0.5) {
                        $ers .=  '' . $value . "\n";
                    } else if ($iblock['weight'] == 0) {
                        $ersInPacket .=  '' . $value . "\n";
                    }
                } else if (!empty($isERPack)) {
                    if (!empty($ersInPacket)) {
                        $erPackets .=  ', ' . $value;
                    } else {
                        $erPackets .=  '' . $value;
                    }
                }
            }
        }
        return [
            'allIBlocks' => $allIBlocks,
            'bigIBlocks' => $bigIBlocks,
            'smallIBlocks' => $smallIBlocks,
            'freeIBlocks' => $freeIBlocks,
            'starLtBlocks' => $starLtBlocks,
            'ers' => $ers,
            'erPackets' => $erPackets,
            'ersInPacket' => $ersInPacket,



        ];
    }


    protected function getAddressString($bxAdressRq)
    {
        $advalue = '';

        if (!empty($bxAdressRq['POSTAL_CODE'])) {
            $advalue = $advalue . $bxAdressRq['POSTAL_CODE'] . ', ';
        }

        if (!empty($bxAdressRq['COUNTRY'])) {
            $advalue = $advalue . $bxAdressRq['COUNTRY'] . ', ';
        }

        if (!empty($bxAdressRq['PROVINCE'])) {
            $advalue = $advalue . $bxAdressRq['PROVINCE'] . ', ';
        }

        if (!empty($bxAdressRq['REGION'])) {
            $advalue = $advalue . $bxAdressRq['REGION'] . ', ';
        }
        if (!empty($bxAdressRq['CITY'])) {
            $advalue = $advalue . $bxAdressRq['CITY'] . ', ';
        }
        if (!empty($bxAdressRq['ADDRESS_1'])) {
            $advalue = $advalue . $bxAdressRq['ADDRESS_1'] . ', ';
        }
        if (!empty($bxAdressRq['ADDRESS_2'])) {
            $advalue = $advalue . $bxAdressRq['ADDRESS_2'] . ', ';
        }

        return $advalue;
    }



    //contract document data
    protected function getDocumentData(
        $contractType,

        // header
        $clientRq, //header and rq and general
        $providerRq,

        //  //specification 2 prices
        $arows,
        $total,
        $contractProductName,
        $isProduct,
        $contractCoefficient,

        //specification 1 tech fields
        $products,
        $contractGeneralFields,
        // $clientRq,
        $clientRqBank,

    ) {

        $products = $this->getProducts(
            $arows,
            $contractProductName,
            $isProduct,
            $contractCoefficient
        );
        $header = $this->getContractHeaderText(
            $contractType,
            $clientRq,
            $providerRq,
            // $clientCompanyFullName,
            // $clientCompanyDirectorNameCase,   //директор | ип | ''
            // $clientCompanyDirectorPositionCase,
            // $clientCompanyBased,
            // $providerCompanyFullName,
            // $providerCompanyDirectorNameCase,
            // $providerCompanyDirectorPositionCase,
            // $providerCompanyBased,
        );
        // $specification =

        return [
            'products' =>  $products,
            'header' =>  $header,
            // 'documentNumber' =>  $documentNumber,
            // 'contractSum' =>  $contractSum,
        ];
    }

    protected function getContractHeaderText(
        $contractType,
        // $clientCompanyFullName,
        // $clientCompanyDirectorNameCase,   //директор | ип | ''
        // $clientCompanyDirectorPositionCase,
        // $clientCompanyBased,
        // $providerCompanyFullName,
        // $providerCompanyDirectorNameCase,
        // $providerCompanyDirectorPositionCase,
        // $providerCompanyBased,
        $clientRq,
        $providerRq,
    ) {

        $clientRole = 'Заказчик';
        $providerRole = 'Исполнитель';
        switch ($contractType) {
            case 'abon':
            case 'key':
                $clientRole = 'Покупатель';
                $providerRole = 'Постващик';
                break;
            case 'lic':
                $clientRole = 'Лицензиат';
                $providerRole = 'Лицензиар';
                break;
            default:
                $clientRole = 'Заказчик';
                $providerRole = 'Исполнитель';
                # code...
                break;
        }
        $providerCompanyFullName = $providerRq['fullname'];
        $providerCompanyDirectorNameCase = $providerRq['director'];
        $providerCompanyDirectorPositionCase = $providerRq['position'];
        $providerCompanyBased = 'Устава';

        $clientCompanyFullName = '';
        $clientCompanyDirectorNameCase = '';
        $clientCompanyDirectorPositionCase = '';
        $clientCompanyBased = '';

        foreach ($clientRq as $rqItem) {
            if ($rqItem['code'] === 'fullname') {
                $clientCompanyFullName = $rqItem['value'];
            } else  if ($rqItem['code'] === 'position_case') {
                $clientCompanyDirectorPositionCase = $rqItem['value'];
            } else  if ($rqItem['code'] === 'director_case') {
                $clientCompanyDirectorNameCase = $rqItem['value'];
            } else  if ($rqItem['code'] === 'based') {
                $clientCompanyBased = $rqItem['value'];
            }
        }

        $headerText = $providerCompanyFullName . ' , официальный партнер компании "Гарант",
        именуемый в дальнейшем "' . $providerRole . '", в лице ' . $providerCompanyDirectorPositionCase . ' ' . $providerCompanyDirectorNameCase . ', действующего(-ей) на основании'
            . $providerCompanyBased . ' с одной стороны и ' . $clientCompanyFullName . ',
        именуемое(-ый) в дальнейшем "' . $clientRole . '", в лице ' . $clientCompanyDirectorPositionCase . ' ' . $clientCompanyDirectorNameCase . ', действующего(-ей) на основании'
            . $clientCompanyBased . ' с другой стороны, заключили настоящий Договор о нижеследующем:';


        return $headerText;
    }

    protected function getDocumentNumber() {}
    protected function getProducts($arows, $contractName, $isProduct, $contractCoefficient)
    {
        $contractFullName = $contractName;
        if ($isProduct) {
            $contractFullName = $contractFullName . ' длительность ' . $contractCoefficient . ' мес. ';
        }

        $products = [];
        foreach ($arows as $key => $row) {
            $product = [
                'productNumber' => $key + 1,
                'productName' => $contractFullName . '(' . $row['name'] . ')',
                'productQuantity' => $row['price']['quantity'],
                'productMeasure' => $row['price']['measure']['name'],
                'productPrice' => $row['price']['current'],
                'productSum' => $row['price']['sum'],
            ];
            array_push($products, $product);
        }

        return $products;
    }
}
