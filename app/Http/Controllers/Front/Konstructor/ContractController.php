<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\ALogController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CounterController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FileController;

use App\DTO\DocumentContract\DocumentContractDataDTO;
use App\Http\Controllers\PDFDocumentController;
use App\Http\Requests\GetContractDocumentRequest;
use App\Http\Resources\PortalContractResource;
use App\Models\Portal;
use App\Models\PortalContract;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\Shared\Converter;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\Response;
use morphos\Russian\MoneySpeller;
use function morphos\Russian\inflectName;
use morphos\Russian\Cases;
use morphos\Russian\NounDeclension;

class ContractController extends Controller
{

    public function frontInit(Request $request) //by id
    {
        $data = $request->all();
        $domain = $data['domain'];
        $companyId = $data['companyId'];
        $contractType = $data['contractType']; //service | product

        $contract = $data['contract'];

        $contract =   $data['contract'];

        $generalContractModel = $data['contract'];
        if (!empty($contract['contract'])) {
            $generalContractModel =  $contract['contract'];
        }
        ALogController::push('old contract', $data);
        $generalContractModel = $contract['contract'];
        $contractQuantity = $generalContractModel['coefficient'];

        $productSet = $data['productSet'];
        $products = $data['products'];
        $contractProductName = $generalContractModel['productName'];
        $arows = $data['arows'];


        $currentComplect = $data['complect']; //lt  //ltInPacket
        $consaltingProduct = $data['consalting']['product'];
        $lt = $data['legalTech'];
        $starProduct = $data['star']['product'];
        $documentInfoblocks =  $data['documentInfoblocks'];
        $isSupplyReport = false;
        $total = null;
        if (!empty($data['total'])) {
            if (!empty($data['total'][0])) {
                $total = $data['total'][0];
            }
        }

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
                'contract' => $this->getContractGeneralForm($arows, $contractQuantity),
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
                if (!empty($rqResponse[0])) {
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
            $errorData = [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];
            return APIController::getError(
                $th->getLine(),
                ['companyId' => $companyId, 'domain' => $domain, 'errorData' => $errorData]
            );
        }
    }



    public function getDocument(GetContractDocumentRequest $request): Response
    {
        // $data = new ($request->all());
        $data = DocumentContractDataDTO::fromRequest($request);

        $method = $data->isSupplyReport ? 'getSupplyDocument' : 'getContractDocument';
        return $this->$method($data);
    }
    public function getContractDocument(DocumentContractDataDTO $data)
    {
        $contractLink = '';
        // $data = $request->all();
        $domain = $data->domain;
        $dealId = $data->dealId;
        $companyId = $data->companyId;
        $contractType = $data->contractType;
        $supplyType = $data->supply->type; //internet | proxima
        $contract = $data->contract;
        $generalContractModel = $contract->contract;
        // $contractQuantity = $generalContractModel['coefficient'];

        // $productSet = $data['productSet']; //все продукты rows из general в виде исходного стэйт объекта

        $products = $data->products;  //productsFromRows  объекты продуктов с полями для договоров полученные из rows
        $contractProductName = $generalContractModel->productName; // приставка к имени продукта из current contract
        $isProduct = $contractType !== 'service';
        $contractCoefficient = $contract->prepayment;

        $pbxCompanyItems = null;
        if (isset($data->bxCompanyItems)) {
            $pbxCompanyItems = $data->bxCompanyItems;
        }

        $pbxDealItems = null;
        if (isset($data->bxDealItems)) {
            $pbxDealItems = $data->bxDealItems;
        }


        $arows = $data->arows; //все продукты rows из general в виде массива
        // $total = $productSet['total'][0];

        $clientType = 'commerc';  //xz зачем это
        if (!empty($data->clientType)) {
            if (!empty($data->clientType['code'])) {
                if ($data->clientType['code'] == 'org_state') {
                    $clientType = 'state';
                }
            }
        }

        $currentClientType = 'org';
        if (!empty($data->clientType)) {
            if (!empty($data->clientType['code'])) {
                $currentClientType = $data->clientType['code'];
            }
        }

        $contractGeneralFields = $data->contractBaseState['items']; //fields array

        // $contractClientState = $data->contractClientState['client'];
        // $clientRq = $contractClientState['rqs']['rq'];                //fields array
        // $clientRqBank = $contractClientState['rqs']['bank'];
        $clientRq = $data->bxrq;
        $providerState = $data->contractProviderState;

        $providerRq = $providerState['current']['rq'];
        $specification = $data->contractSpecificationState['items'];
        $total = null;
        if (!empty($data->total)) {
            if (!empty($data->total[0])) {
                $total =  $data->total[0];
            };
        };
        $contractSum = $total['price']['sum'];
        $totalProductName = $total['name'];
        $contractProductTitle = $products[0]->name . '_' . $products[0]->supplyName . '_' . $products[0]->contract->aprilName;
        $contractSum = number_format($contractSum, 2, '.', ''); // "8008.00"

        $moneySpeller = new MoneySpeller();

        // Преобразуем сумму в строку
        $contractSumString = $moneySpeller->spell($contractSum, 'RUB');
        $contractSumString = '(' .        $contractSumString . ')';

        // $etalonPortal = Portal::where('domain', 'april-dev.bitrix24.ru')->first();
        // $template = $etalonPortal->templates()
        //     ->where('type', 'contract')
        //     ->where('code', 'proxima')
        //     ->first();
        // $templateField = $template->fields()
        //     ->where('type', 'template')
        //     ->where('code', 'proxima')
        //     ->first();

        // $templatePath = $templateField['value'];
        // if (substr($templatePath, 0, 8) === '/storage') {
        //     $relativePath = substr($templatePath, 8); // Обрезаем первые 8 символов
        // }

        // // Проверяем, существует ли файл
        // // if (Storage::disk('public')->exists($relativePath)) {
        //     // Строим полный абсолютный путь
        //     $fullPath = storage_path('app/public') . '/' . $relativePath;

        // Теперь $fullPath содержит полный путь к файлу
        // $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);


        $filePath = 'app/public/konstructor/templates/contract/etalon/' . $contractType . '/' . $supplyType . '/' . $clientType;

        $fullPath = storage_path($filePath . '/template.docx');
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);



        $templateData = $this->getDocumentData(
            $domain,
            $contractType,

            // header
            $clientRq, //header and rq and general
            $currentClientType,
            $providerRq,

            //  //specification 2 prices
            $arows,
            // $total,
            $contractProductName,
            $isProduct,
            $contractCoefficient,

            //specification 1 tech fields
            $products,
            $specification,
            $contractGeneralFields,
            // $clientRq,
            // $clientRqBank,
            $totalProductName,

            $pbxCompanyItems,
            $pbxDealItems,
            $total,

            // general dates and sums at body

        );
        //templatecontent


        $documentNumber = CounterController::getCount($providerRq['id'], 'contract');


        $templateProcessor->setValue('contract_number', $documentNumber);

        $templateProcessor->setValue('header', $templateData['header']);
        // $templateProcessor->cloneRowAndSetValues('productNumber', $templateData['products']);
        // $templateProcessor->setValue('contract_total_sum', $contractSum);

        // $templateProcessor->setValue('contract_total_sum_string', $contractSumString);





        foreach ($templateData['general'] as $gcode => $g) {
            $gformattedSpec = str_replace("\n", '</w:t><w:br/><w:t>', $g);

            $templateProcessor->setValue($gcode, $gformattedSpec);
        };

        foreach ($templateData['rq'] as $rqcode => $rqItem) {
            $rqItemformattedSpec = str_replace("\n", '</w:t><w:br/><w:t>', $rqItem);

            $templateProcessor->setValue($rqcode, $rqItemformattedSpec);
        };

        foreach ($templateData['total'] as $code => $totalItemValue) {
            // $formattedSpec = str_replace("\n", '</w:t><w:br/><w:t>', $spec);
            $templateProcessor->setValue($code, $totalItemValue);
            // $templateProcessor->setValue($code, $spec);
        }

        foreach ($templateData['specification'] as $code => $spec) {
            $formattedSpec = str_replace("\n", '</w:t><w:br/><w:t>', $spec);
            $templateProcessor->setValue($code, $formattedSpec);
            // $templateProcessor->setValue($code, $spec);
        }
        if ($contractType !== 'lic') {
            $templateProcessor->cloneRowAndSetValues('productNumber', $templateData['productRows']);
        }

        // Дальнейшие действия с документом...
        $resultPath = storage_path('app/public/clients/' . $data->domain . '/documents/contracts/' . $data->userId);

        if (!file_exists($resultPath)) {
            // Log::channel('telegram')->error('APRIL_ONLINE', [
            //     'resultPath' => $resultPath
            // ]);

            mkdir($resultPath, 0775, true); // Создать каталог с правами доступа
        }
        if (!is_writable($resultPath)) {
            Log::channel('telegram')->error('APRIL_ONLINE', [
                '!is_writable resultPath' => $resultPath
            ]);
            throw new \Exception("Невозможно записать в каталог: $resultPath");
        }
        $resultFileName = $contractProductTitle . '_договор.docx';
        $outputFileName = 'Договор ' . $contractProductTitle . ' ' . $contractType;
        $templateProcessor->saveAs($resultPath . '/' . $resultFileName);

        // sleep(1);
        // // Преобразуем DOCX в RTF
        // $rtfFileName = $contractProductTitle . '_договор.rtf';
        // $phpWord = \PhpOffice\PhpWord\IOFactory::load($resultPath . '/' . $resultFileName);
        // $rtfWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'RTF');
        // $rtfWriter->save($resultPath . '/' . $rtfFileName);


        $contractLink = asset('storage/clients/' . $domain . '/documents/contracts/' . $data->userId . '/' . $resultFileName);

        $method = '/crm.timeline.comment.add';
        $hook = BitrixController::getHook($domain);

        $url = $hook . $method;

        $message = '<a href="' . $contractLink . '" target="_blank">' . $outputFileName . '</a>';

        $fields = [
            "ENTITY_ID" => $dealId,
            "ENTITY_TYPE" => 'deal',
            "COMMENT" => $message
        ];
        $data = [
            'fields' => $fields
        ];
        $responseBitrix = Http::get($url, $data);

        $fields = [
            "ENTITY_ID" => $companyId,
            "ENTITY_TYPE" => 'company',
            "COMMENT" => $message
        ];
        $data = [
            'fields' => $fields
        ];
        $responseBitrix = Http::get($url, $data);

        // } else {
        //     return APIController::getError(
        //         'шаблон не найден',
        //         ['contractData' => $data, 'link' => $relativePath, 'template' => $template, 'templateField' => $templateField]
        //     );
        // }       // // Создаем экземпляр обработчика шаблона
        // $templateProcessor = new TemplateProcessor($templatePath);

        // // Замена заполнителей простыми значениями
        // $templateProcessor->setValue('name', 'John Doe');
        // $templateProcessor->setValue('address', '123 Main Street');

        // // Предположим, что у нас есть массив данных для таблицы
        // $products = [
        //     ['name' => 'Product A', 'quantity' => 2],
        //     ['name' => 'Product B', 'quantity' => 5]
        // ];

        // // Добавление строк в таблицу
        // $templateProcessor->cloneRowAndSetValues('product_name', $products);

        // // Сохраняем измененный документ
        // $savePath = 'path/to/output.docx';
        // $templateProcessor->saveAs($savePath);

        return APIController::getSuccess(
            ['result' => ['contractData' => $data, 'link' => $contractLink]]
        );
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
        $result = [
            'rq' => [

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
                [
                    'type' => 'string',
                    'name' => 'Сокращенное наименование организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'shortname',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2,


                ],
                [
                    'type' => 'string',
                    'name' => 'Роль клиента в договоре',
                    'value' => $clientRole,
                    'isRequired' => true,
                    'code' => 'role',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 3

                ],
                [
                    'type' => 'string',
                    'name' => 'Должность руководителя организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'position',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'order' => 4
                ],
                [
                    'type' => 'string',
                    'name' => 'Должность руководителя организации (в лице)',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'position_case',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'order' => 6,

                ],
                [
                    'type' => 'string',
                    'name' => 'ФИО руководителя организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'director',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 5,

                ],
                [
                    'type' => 'string',
                    'name' => 'ФИО руководителя организации (в лице)',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'director_case',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 7

                ],

                [
                    'type' => 'string',
                    'name' => 'Действующий на основании',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'based',
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 8

                ],


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
                [
                    'type' => 'string',
                    'name' => 'КПП',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'kpp',
                    'includes' => ['org', 'org_state'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 10

                ],

                [
                    'type' => 'string',
                    'name' => 'ОГРН',
                    'value' => '',
                    'isRequired' => false,
                    'code' => 'ogrn',
                    'includes' => ['org', 'org_state'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 11

                ],

                [
                    'type' => 'string',
                    'name' => 'ОГРНИП',
                    'value' => '',
                    'isRequired' => false,
                    'code' => 'ogrnip',
                    'includes' => ['ip'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 12

                ],

                [
                    'type' => 'string',
                    'name' => 'ФИО главного бухгалтера организации',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'accountant',
                    'includes' => ['org', 'org_state'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 13

                ],


                [
                    'type' => 'string',
                    'name' => 'Телефон',
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
                [
                    'type' => 'string',
                    'name' => 'ФИО',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'personName',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'header',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 0

                ],


                [
                    'type' => 'string',
                    'name' => 'Вид Документа',
                    'value' => 'Паспорт',
                    'isRequired' => true,
                    'code' => 'document',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 1

                ],
                [
                    'type' => 'string',
                    'name' => 'Серия Документа',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docSer',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 2

                ],
                [
                    'type' => 'string',
                    'name' => 'Номер Документа',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docNum',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 3

                ],
                [
                    'type' => 'string',
                    'name' => 'Дата Выдачи Документа',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docDate',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 4

                ],
                [
                    'type' => 'string',
                    'name' => 'Документ выдан подразделением',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docDate',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 5

                ],
                [
                    'type' => 'string',
                    'name' => 'Код подразделения',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'docDate',
                    'includes' => ['fiz', 'advokat'],
                    'group' => 'rq',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 6

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
        if (!empty($bxAdressesRq) && is_array($bxAdressesRq)) {
            foreach ($bxAdressesRq as $bxAdressRq) {
                $isRegisrted = false;
                $isFizRegisrted = false;
                if ($bxAdressRq['TYPE_ID'] === 4) { // Адрес регистрации
                    $isRegisrted = true;

                    if (!empty($bxAdressRq['ADDRESS_1'])) {
                        $advalue = $this->getAddressString($bxAdressRq);

                        // foreach ($result['address'] as $resultAddress) {
                        //     if ($resultAddress['code'] == 'registredAdress' && $resultAddress['name'] ==  'Адрес прописки') {
                        //         $resultAddress['value'] = $advalue;
                        //     }
                        // }
                        for ($i = 0; $i < count($result['address']); $i++) {
                            if ($result['address'][$i]['code'] === 'registredAdress'  && $result['address'][$i]['name'] ==  'Адрес прописки') {
                                $result['address'][$i]['value'] = $advalue;
                                array_push($result['rq'], $result['address'][$i]);
                            }
                        }
                    }
                } else  if ($bxAdressRq['TYPE_ID'] === 6) {  // Юридический адрес
                    if (!empty($bxAdressRq['ADDRESS_1'])) {
                        $advalue = $this->getAddressString($bxAdressRq);

                        // foreach ($result['address'] as $resultAddress) {
                        //     if ($resultAddress['code'] == 'registredAdress') {
                        //         $resultAddress['value'] = $advalue;
                        //     }
                        // }

                        for ($i = 0; $i < count($result['address']); $i++) {
                            if ($result['address'][$i]['code'] === 'registredAdress') {
                                $result['address'][$i]['value'] = $advalue;
                                array_push($result['rq'], $result['address'][$i]);
                            }
                        }
                    }
                } else {
                    if (!empty($bxAdressRq['ADDRESS_1'])) {
                        $advalue = $this->getAddressString($bxAdressRq);

                        // foreach ($result['address'] as $resultAddress) {
                        //     if ($resultAddress['code'] == 'primaryAdresss') {
                        //         $resultAddress['value'] = $advalue;
                        //     }
                        // }

                        for ($i = 0; $i < count($result['address']); $i++) {
                            if ($result['address'][$i]['code'] === 'primaryAdresss') {
                                $result['address'][$i]['value'] = $advalue;
                                array_push($result['rq'], $result['address'][$i]);
                            }
                        }
                    }
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
        return ['rq' => $result['rq'], 'bank' => $result['bank']];
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
                'isRequired' => false,
                'code' => 'prepayment_start',
                'group' => 'contract',
                'isActive' => false,
                'isDisable' => false,
                'order' => 5,
                'includes' => ['org', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Предоплата по',
                'value' => '',
                'isRequired' => false,
                'code' => 'prepayment_finish',
                'group' => 'contract',
                'isActive' => false,
                'isDisable' => false,
                'order' => 6,
                'includes' => ['org',  'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Внести предоплату не позднее',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_pay_date',
                'group' => 'contract',
                'isActive' => false,
                'isDisable' => false,
                'order' => 7,
                'includes' => ['org',  'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Период в подарок с',
                'value' => '',
                'isRequired' => false,
                'code' => 'present_start',
                'group' => 'contract',
                'isActive' => false,
                'isDisable' => false,
                'order' => 8,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],


            ],
            [
                'type' => 'date',
                'name' => 'Период в подарок по',
                'value' => '',
                'isRequired' => false,
                'code' => 'present_finish',
                'group' => 'contract',
                'isActive' => false,
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
                'name' => 'Сумма предоплаты',
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
                'isDisable' => true,
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

        if ($product['supplyType'] === 'internet') {
            $contractSupplyName .=  ' ОД';
        }
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

        $products_names = '';

        foreach ($products as $prdct) {
            $products_names .= $prdct['name'] . " \n";
        };


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

        if (empty($consaltingComment)) {
            foreach ($products as $product) {
                if (empty($consaltingComment)) {
                    if (!empty($product)) {
                        if (!empty($product['withConsalting'])) {
                            if (!empty($product['contractConsaltingComment'])) {
                                $consaltingcomment = $product['contractConsaltingComment'];
                            }
                            if (!empty($product['contractConsaltingProp'])) {
                                $consalting = $product['contractConsaltingComment'];
                                $consaltingName = 'Советы экспертов. Проверки, налоги, право';
                            }
                        }
                    }
                }
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
                // [
                //     'type' => 'string',
                //     'name' => 'Email для интернет-версии',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'specification_email',
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 0,
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'contractType' => ['service', 'lic', 'abon', 'key'],
                //     'supplies' => ['internet'],

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'ФИО ответственного за получение справочника',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'assigned',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 0,
                //     'contractType' => ['service', 'lic', 'abon', 'key'],
                //     'supplies' => ['internet', 'proxima'],

                // ],
                // [
                //     'type' => 'string',
                //     'name' => 'Телефон ответственного за получение справочника',
                //     'value' => '',
                //     'isRequired' => true,
                //     'code' => 'assignedPhone',
                //     'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                //     'group' => 'specification',
                //     'isActive' => true,
                //     'isDisable' => false,
                //     'order' => 1,
                //     'contractType' => ['service', 'lic', 'abon', 'key'],
                //     'supplies' => ['internet', 'proxima'],


                // ],
                [
                    'type' => 'text',
                    'name' => 'Наименование ',
                    'value' => $products_names,
                    'isRequired' => false,
                    'code' => 'complect_name',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
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
                [
                    'type' => 'text',
                    'name' => 'Вид Размещения',
                    'value' => $contractSupplyName,
                    'isRequired' => false,
                    'code' => 'specification_supply',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 1.5,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
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
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true



                ],
                [
                    'type' => 'string',
                    'name' => 'ПК/ГЛ договор',
                    'value' => $consalting,
                    'isRequired' => false,
                    'code' => 'specification_pk_comment1',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 2,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true

                ],
                [
                    'type' => 'text',
                    'name' => 'Комментарий к ПК/ГЛ',
                    'value' => $consaltingcomment,
                    'isRequired' => false,
                    'code' => 'specification_pk_comment',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => true,
                    'order' => 3,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'text',
                    'name' => 'Информационные блоки',
                    'value' => $iblocks['allIBlocks'],
                    'isRequired' => false,
                    'code' => 'specification_iblocks',
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
                    'name' => 'Малые информационные блоки',
                    'value' =>  $iblocks['smallIBlocks'],
                    'isRequired' => false,
                    'code' => 'specification_ismall',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 5,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],
                [
                    'type' => 'text',
                    'name' => 'Большие информационные блоки',
                    'value' =>  $iblocks['bigIBlocks'],
                    'isRequired' => false,
                    'code' => 'specification_ibig',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 5,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


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

                [
                    'type' => 'text',
                    'name' => 'Примечание Вид Размещения',
                    'value' => $product['contractSupplyPropComment'],
                    'isRequired' => false,
                    'code' => 'specification_supply_comment',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 13,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['internet', 'proxima'],
                    'contractType' => ['service', 'lic', 'abon', 'key']


                ],

                [
                    'type' => 'text',
                    'name' => 'Носители, используемые при предоставлении услуг',
                    'value' => $contractSupplyProp1,
                    'isRequired' => true,
                    'code' => 'specification_distributive',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 14,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['proxima'],
                    'contractType' => ['service', 'lic',  'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'text',
                    'name' => 'Примечание Носители',
                    'value' => $contractSupplyPropEmail,
                    'isRequired' => true,
                    'code' => 'specification_distributive_comment',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 15,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['proxima'],
                    'contractType' => ['service', 'lic',  'key'],
                    'isHidden' => true,


                ],
                [
                    'type' => 'text',
                    'name' => 'Носители дистрибутивов предоставляются следующим способом',
                    'value' => $contractSupplyProp2,
                    'isRequired' => true,
                    'code' => 'specification_dway',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 16,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['proxima'],
                    'contractType' => ['service', 'lic',  'key'],
                    'isHidden' => true


                ],
                [
                    'type' => 'text',
                    'name' => 'Примечание к способу',
                    'value' => '',
                    'isRequired' => true,
                    'code' => 'specification_dway_comment',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 17,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'supplies' => ['proxima'],
                    'contractType' => ['service', 'lic',  'key'],
                    'isHidden' => true,


                ],
                [
                    'type' => 'string',
                    'name' => 'Периодичность предоставления услуг',
                    'value' => '1 неделя',
                    'isRequired' => true,
                    'code' => 'specification_service_period',
                    'group' => 'specification',
                    'isActive' => true,
                    'isDisable' => false,
                    'order' => 18,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['service', 'lic'],
                    'supplies' => ['proxima'],

                ],
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

                [
                    'type' => 'text',
                    'name' => 'Email прмечание',
                    'value' => $contractSupplyPropEmail,
                    'isRequired' => true,
                    'code' => 'specification_email_comment',
                    'group' => 'specification',
                    'isActive' => false,
                    'isDisable' => false,
                    'order' => 22,
                    'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                    'contractType' => ['service', 'lic', 'abon', 'key'],
                    'supplies' => ['internet'],
                    'isHidden' => true,


                ],
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


            ],
            // [
            //     'type' => 'string',
            //     'name' => 'Комментарий к наименованию',
            //     'value' => '',
            //     'isRequired' => true,
            //     'code' => 'contract_spec_products_names_comment',
            //     'group' => 'specification',
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

                'value' =>  [
                    'id' => 0,
                    'code' => 'yes',
                    'name' => 'Да',
                    'title' => 'Да'
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
                        'title' => 'Да'
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


            ],
            [
                'type' => 'select',
                'name' => 'Занесена в АРМ',

                'value' =>  [
                    'id' => 0,
                    'code' => 'yes',
                    'name' => 'Да',
                    'title' => 'Да'
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
                        'title' => 'Да'
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


            ],
            [
                'type' => 'string',
                'name' => 'Менеджер отдела продаж',
                'value' => '',
                'isRequired' => true,
                'code' => 'manager',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 4,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'string',
                'name' => 'Менеджер отдела ТМЦ',
                'value' => '',
                'isRequired' => true,
                'code' => 'tmc',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 4,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'select',
                'name' => 'Создавался ли Договор',
                'type' => 'select',
                'value' =>  [
                    'id' => 0,
                    'code' => 'yes',
                    'name' => 'Да',
                    'title' => 'Да'
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
                        'title' => 'Да'
                    ],

                ],
                'isRequired' => true,
                'code' => 'is_contract_done',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 5,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'string',
                'name' => 'Дата и номер договора',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_number',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => true,
                'order' => 6,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'text',
                'name' => 'Судьба договора',
                'value' => '',
                'isRequired' => true,
                'code' => 'contract_result',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 7,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],

            [
                'type' => 'select',
                'name' => 'Создавался ли Счет',
                'type' => 'select',
                'value' =>  [
                    'id' => 0,
                    'code' => 'yes',
                    'name' => 'Да',
                    'title' => 'Да'
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
                        'title' => 'Да'
                    ],

                ],
                'isRequired' => true,
                'code' => 'is_invoice_done',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 8,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'string',
                'name' => 'Дата и номер счета',
                'value' => '',
                'isRequired' => true,
                'code' => 'invoice_number',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 9,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'text',
                'name' => 'Его судьба',
                'value' => '',
                'isRequired' => true,
                'code' => 'invoice_result',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 10,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'date',
                'name' => 'Дата оплаты Счета',
                'value' =>  '',
                'isRequired' => true,
                'code' => 'invoice_pay_date',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 11,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


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
                'order' => 12,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'string',
                'name' => 'Компания в битрикс',
                'value' => '',
                'isRequired' => true,
                'code' => 'bitrix_company',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 13,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],

            [
                'type' => 'string',
                'name' => 'Сделка в битрикс',
                'value' => '',
                'isRequired' => true,
                'code' => 'bitrix_deal',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 14,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'text',
                'name' => 'Источник',
                'value' => "",
                'isRequired' => true,
                'code' => 'source',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 15,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'select',
                'name' => 'Регион',
                'value' =>  [
                    'id' => 0,
                    'code' => 'org',
                    'name' => 'Организация Коммерческая',
                    'title' => 'Организация Коммерческая'
                ],
                'isRequired' => true,

                'items' => [
                    [
                        'id' => 0,
                        'code' => 'ro',
                        'name' => 'РО',
                        'title' => 'РО'
                    ],
                    [
                        'id' => 1,
                        'code' => 'ko',
                        'name' => 'KО',
                        'title' => 'KО'
                    ],
                    [
                        'id' => 2,
                        'code' => 'sk',
                        'name' => 'CK',
                        'title' => 'СК'
                    ],

                    [
                        'id' => 3,
                        'code' => 'lo',
                        'name' => 'ЛО',
                        'title' => 'ЛО'
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
                        'name' => 'СПБ',
                        'title' => 'СПБ'
                    ],

                ],
                'code' => 'company_type',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 16,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],

            [
                'type' => 'text',
                'name' => 'Описание ситуации, примечания, дополнительные сведения',
                'value' => '',
                'isRequired' => true,
                'code' => 'situation_comments',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 18,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],

            ],
            [
                'type' => 'text',
                'name' => 'Контактные лица',
                'value' => '',
                'isRequired' => true,
                'code' => 'company_contacts',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 19,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'string',
                'name' => 'Наличие конкурентов',
                'value' =>  '',
                'isRequired' => true,
                'code' => 'concurents',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 20,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],


            ],
            [
                'type' => 'select',
                'name' => 'Что известно',
                'value' => [
                    'id' => 1,
                    'code' => 'success',
                    'name' => 'Обслуживаются',
                    'title' => 'Обслуживаются'
                ],
                'isRequired' => true,
                'code' => 'supply_information',
                'group' => 'supply',
                'isActive' => true,
                'isDisable' => false,
                'order' => 21,
                'includes' => ['org', 'org_state', 'ip', 'advokat', 'fiz'],
                'supplies' => ['internet', 'proxima'],
                'contractType' => ['service', 'lic', 'abon', 'key'],
                'items' => [
                    [
                        'id' => 0,
                        'code' => 'fail',
                        'name' => 'Отказались',
                        'title' => 'Отказались'
                    ],
                    [
                        'id' => 1,
                        'code' => 'success',
                        'name' => 'Обслуживаются',
                        'title' => 'Обслуживаются'
                    ],


                ],

            ],
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
                'isActive' => true,
                'isDisable' => false,
                'order' => 22,
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

            ],


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
        $domain,
        $contractType,

        // header
        $clientRq, //header and rq and general
        $currentClientType, // org org_state ip fiz
        $providerRq,

        //  //specification 2 prices
        $arows,
        // $total,
        $contractProductName,
        $isProduct,
        $contractCoefficient,

        //specification 1 tech fields
        $products,
        $specification,
        $contractGeneralFields,
        // $clientRq,
        // $clientRqBank,
        $totalProductName,
        $pbxCompanyItems,
        $pbxDealItems,
        $total

    ) {
        Carbon::setLocale('ru');
        $documentData = array(
            'contract_number' => '',
            'contract_city' => '',
            'contract_date' => '',
            'header' => '',
            'client_assigned_fio' => '',
            'contract_total_sum' => '',
            'contract_total_sum_string' => '',
            'contract_pay_date' => '',
            'we_rq' => '',
            'we _role' => '',
            'we _direct_position' => '',
            'we _direct_fio' => '',
            'client_rq' => '',
            'client _role' => '',
            'client_direct_position' => '',
            'client _direct_fio' => '',
            'infoblocks' => '',
            'legal_techs' => '',
            'supply_contract' => '',
            'logins_quantity' => '',
            'lic_long' => '',
            'contract_internet_email' => '',


        );
        $contract_date = '«____» _________________ 20__ г.';
        $contract_pay_date = '«____» _________________ 20__ г.';
        $contract_start = '«____» _________________ 20__ г.';
        $contract_end = '«____» _________________ 20__ г.';
        $client_assigned_fio = ' _____________________________ ';
        $garant_client_email = '________________________________';
        if (!empty($pbxDealItems)) {
            if (!empty($pbxDealItems['contract_start']) && !empty($pbxDealItems['contract_start']['current'])) {
                $contract_start = $pbxDealItems['contract_start']['current'];
                $contract_start = mb_strtolower(
                    Carbon::parse($contract_start)
                        ->translatedFormat('j F Y')
                ) . ' г.';
            }
            if (!empty($pbxDealItems['contract_end']) && !empty($pbxDealItems['contract_end']['current'])) {
                $contract_end = $pbxDealItems['contract_end']['current'];
                $contract_end = mb_strtolower(
                    Carbon::parse($contract_end)
                        ->translatedFormat('j F Y')
                ) . ' г.';
            }

            if (!empty($pbxDealItems['contract_create_date']) && !empty($pbxDealItems['contract_create_date']['current'])) { //дата создания договора
                $contract_date = $pbxDealItems['contract_create_date']['current'];
                if (!empty($contract_date)) {

                    $contract_date = mb_strtolower(
                        Carbon::parse($contract_date)
                            ->translatedFormat('j F Y')
                    ) . ' г.';
                }
            }

            if (!empty($pbxDealItems['garant_client_assigned_name']) && !empty($pbxDealItems['garant_client_assigned_name']['current'])) { //ФИО Ответственного за получение справочника
                $client_assigned_fio = $pbxDealItems['garant_client_assigned_name']['current'];
            }

            if (!empty($pbxDealItems['first_pay_date']) && !empty($pbxDealItems['first_pay_date']['current'])) { //Внести оплату не позднее
                $contract_pay_date = $pbxDealItems['first_pay_date']['current'];
                if (!empty($contract_pay_date)) {
                    Carbon::setLocale('ru');
                    $contract_pay_date = mb_strtolower(
                        Carbon::parse($contract_pay_date)
                            ->translatedFormat('j F Y')
                    ) . ' г.';
                }
            }
            if (!empty($pbxDealItems['garant_client_email'])) { //Внести оплату не позднее
                $garant_client_email = $pbxDealItems['garant_client_email']['current'];
            }
        }
        $productRows = $this->getProducts(
            $arows,
            $contractProductName,
            $isProduct,
            $contractCoefficient,
            $currentClientType
        );
        $totalData = $this->getTotal($total, $currentClientType);
        $header = $this->getContractHeaderText(
            $contractType,
            $currentClientType,
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



        $specificationData = $this->getSpecificationCDatareate(
            $domain,
            $specification
        );



        // foreach ($contractGeneralFields as $row) {
        //     if ($row['code'] == 'contract_create_date') {
        //         $contract_date = $row['value'];
        //     }
        //     if ($row['code'] == 'contract_pay_date') {
        //         $contract_pay_date = $row['value'];
        //     }
        // }




        $general = [
            'contract_date' => $contract_date,
            'contract_city' => '',
            'contract_pay_date' => $contract_pay_date,
            'contract_start' => $contract_start,
            'contract_end' => $contract_end,
            // 'contract_pay_date' => $contract_pay_date,
            'client_assigned_fio' => $client_assigned_fio,
            'garant_client_email' => $garant_client_email
        ];






        $we_rq = $providerRq['shortname'] . "\n" . "\n"
            . 'Адрес: ' . $providerRq['registredAdress'] . "\n"
            . "ИНН: " . $providerRq['inn'] . "\n"
            . "Р/с: " . $providerRq['rs'] . "\n"
            . "К/с: " . $providerRq['ks'] . "\n"
            . $providerRq['bank'] . "\n"
            . 'Телефон.: ' . $providerRq['phone'] . "\n"
            . 'E-mail: ' . $providerRq['email'] . "\n";

        $client_rq = $this->getClientRQ($currentClientType, $clientRq);


        $roles = $this->getRoles($contractType);

        $we_direct_fio = $this->getSurnameAndInitials($providerRq['director']);
        $providerType = $providerRq['type'];
        $we_direct_position =  $providerRq['position'];
        if ($providerType == 'ip') {
            $we_direct_position = '';
            $we_direct_fio = $providerRq['shortname'];
        }

        $client_direct_position = '';
        $client_direct_fio = '';

        $clientDirectorFullName = '';
        foreach ($clientRq['fields'] as $rqItem) {

            if (!empty($rqItem['value'])) {
                if ($currentClientType == 'org' || $currentClientType == 'org_state') {
                    if ($rqItem['code'] === 'director') {

                        $clientDirectorFullName = $rqItem['value'];
                        $client_direct_fio = $this->getSurnameAndInitials($clientDirectorFullName);
                    } else  if ($rqItem['code'] === 'position') {
                        $client_direct_position = $rqItem['value'];
                    }
                } else if ($currentClientType == 'fiz') {
                    if ($rqItem['code'] === 'personName') {


                        $clientDirectorFullName = $rqItem['value'];
                        $client_direct_fio = $this->getSurnameAndInitials($clientDirectorFullName);
                    }
                } else  if ($currentClientType == 'ip') {
                    if ($rqItem['code'] === 'shortname') {


                        $clientDirectorFullName = $rqItem['value'];
                    }
                }
            }
        }

        $rq = [
            'we_rq' => $we_rq,
            'we_role' => $roles['provider'],
            'we_direct_position' => $we_direct_position,
            'we_direct_fio' => $we_direct_fio,
            'client_rq' => $client_rq['client_rq'],
            'client_role' =>  $roles['client'],
            'client_direct_position' =>   $client_direct_position,
            'client_direct_fio' => $client_direct_fio,
            'client_adress' => $client_rq['client_adress']
        ];


        if (preg_match('/(?:г(?:\.|ород)?)\s+([а-яёА-ЯЁ\-]+)(?=[,\s]|$)/iu', $providerRq['registredAdress'], $matches)) {
            $general['contract_city'] = trim($matches[1]);
        } else {
            // Альтернативная попытка
            if (preg_match('/,\s*([а-яёА-ЯЁ\-]+)\s+г[,]/iu', $providerRq['registredAdress'], $matches)) {
                $general['contract_city'] = trim($matches[1]);
            } else {
                $general['contract_city'] = null; // Если не удалось найти
            }
        }

        if (!empty($general['contract_city'])) {
            $general['contract_city'] = 'г. ' . $general['contract_city'];
        }

        return [
            'productRows' =>  $productRows,
            'products' =>  $products,
            'header' =>  $header,
            'specification' =>  $specificationData,
            'general' =>  $general,
            'rq' => $rq,
            'total' => $totalData
            // 'documentType' =>  $documentType,
            // 'documentName' =>  $documentName,
            // 'documentDate' =>  $documentDate,
            // 'documentSign' =>  $documentSign,
            // 'documentNumber' =>  $documentNumber,
            // 'contractSum' =>  $contractSum,


        ];
    }
    protected function getSurnameAndInitials($fullName)
    {
        // Убираем лишние пробелы
        $fullName = trim($fullName);
        // Разбиваем строку на части
        $parts = explode(' ', $fullName);

        if (count($parts) < 2) {
            return $fullName; // Если не хватает данных, возвращаем оригинальную строку
        }

        // Получаем фамилию
        $surname = $parts[0];

        // Инициал имени
        $nameInitial = isset($parts[1]) ? mb_substr($parts[1], 0, 1) . '.' : '';
        // Инициал отчества (если есть)
        $patronymicInitial = isset($parts[2]) ? mb_substr($parts[2], 0, 1) . '.' : '';

        // Формируем результат
        return $surname . ' ' . $nameInitial . $patronymicInitial;
    }
    public function getClientRQ(

        $currentClientType,
        $clientRq

    ) {



        $companyName = '____________________________________________________';  // || fio

        $inn = '___________________________'; // ||
        $fizDocument = '________________________________'; // ||
        $address = '_____________________________________________'; //
        $bank = '________________________________'; // ||
        $rs = '_________________________________'; // ||
        $ks = '______________________________________'; // ||
        $bik = ''; // ||

        $bankOther = ''; // ||

        $phone = '_________________________________'; // ||
        $email = '__________________________________'; // ||
        $client_adress = '________________________________';



        foreach ($clientRq['fields'] as $rqItem) {

            if ($rqItem['code'] == 'inn') {
                if (!empty($rqItem['value'])) {
                    $inn = $rqItem['value'];
                }
            }
            if ($rqItem['code'] == 'phone') {
                if (!empty($rqItem['value'])) {
                    $phone = $rqItem['value'];
                }
            }
            if ($rqItem['code'] == 'email') {
                if (!empty($rqItem['value'])) {
                    $email = $rqItem['value'];
                }
            }


            if ($rqItem['code'] == 'shortname') {  //fullname
                if ($currentClientType === 'org' || 'org_state') {
                    if (!empty($rqItem['value'])) {
                        $companyName = $rqItem['value'];
                    }
                }
            }

            if ($rqItem['code'] == 'personName') {  //fullname
                if ($currentClientType === 'fiz') {
                    $companyName = $rqItem['value'];
                }
            }

            if ($currentClientType === 'fiz') {
                if ($rqItem['code'] == 'document') {
                    if (!empty($rqItem['value'])) {
                        $fizDocument = $rqItem['value'] . ' ';
                    }
                }
                if ($rqItem['code'] == 'docSer') {
                    if (!empty($rqItem['value'])) {
                        $fizDocument .= 'Серия: ' . $rqItem['value'] . ' ';
                    }
                }
                if ($rqItem['code'] == 'docNum') {
                    if (!empty($rqItem['value'])) {
                        $fizDocument .= 'Номер: ' . $rqItem['value'] . ' ';
                    }
                }
                if ($rqItem['code'] == 'docDate') {
                    if (!empty($rqItem['value'])) {
                        $fizDocument .= 'Дата выдачи: ' . $rqItem['value'];
                    }
                }
            }

            if ($currentClientType === 'org' || 'org_state') {
            } else if ($currentClientType === 'ip') {
            } else if ($currentClientType === 'fiz') {
            }
        }

        if (!empty($clientRq['address'])) {

            if (!empty($clientRq['address']['items'])) {
                foreach ($clientRq['address']['items'] as $rqAddress) {
                    $searchingAddress = '';
                    foreach ($rqAddress['fields'] as $rqAddressField) {

                        if ($rqAddressField['code'] === 'address_country') {
                            if (!empty($rqAddressField['value'])) {

                                $searchingAddress = $rqAddressField['value'] . ', ';
                            }
                        }
                        if ($rqAddressField['code'] === 'address_region') {
                            if (!empty($rqAddressField['value'])) {
                                $searchingAddress .= $rqAddressField['value'] . ', ';
                            }
                        }
                        if ($rqAddressField['code'] === 'address_region') {
                            if (!empty($rqAddressField['value'])) {
                                $searchingAddress .= $rqAddressField['value'] . ', ';
                            }
                        }
                        if ($rqAddressField['code'] === 'address_city') {
                            if (!empty($rqAddressField['value'])) {
                                $searchingAddress .= $rqAddressField['value'] . ', ';
                            }
                        }
                        if ($rqAddressField['code'] === 'address_1') {
                            if (!empty($rqAddressField['value'])) {
                                $searchingAddress .= $rqAddressField['value'] . ', ';
                            }
                        }
                        if ($rqAddressField['code'] === 'address_2') {
                            if (!empty($rqAddressField['value'])) {
                                $searchingAddress .= $rqAddressField['value'];
                            }
                        }
                    }


                    if (!empty($searchingAddress)) {
                        if ($rqAddress['type_id'] == 6) {
                            $address = $searchingAddress;
                        } else {
                            $client_adress = $searchingAddress;
                        }
                    }
                }
            }
        }

        ALogController::push('clientRq', $clientRq);

        if (!empty($clientRq['bank'])) {

            if (!empty($clientRq['bank']['items'])) {
                if (!empty($clientRq['bank']['items'][0])) {
                    if (!empty($clientRq['bank']['items'][0]['fields'])) {
                        foreach ($clientRq['bank']['items'][0]['fields'] as $rqBank) {

                            if ($rqBank['code'] == 'bank_name') {
                                if (!empty($rqBank['value'])) {
                                    $bank = $rqBank['value'];
                                }
                            }
                            if ($rqBank['code'] == 'rs' || $rqBank['code'] == 'bank_pc') {
                                if (!empty($rqBank['value'])) {
                                    $rs = $rqBank['value'];
                                }
                            }
                            if ($rqBank['code'] == 'ks' || $rqBank['code'] == 'bank_kc') {
                                if (!empty($rqBank['value'])) {
                                    $ks = $rqBank['value'];
                                }
                            }
                            if ($rqBank['code'] == 'bik' || $rqBank['code'] == 'bank_bik') {
                                if (!empty($rqBank['value'])) {
                                    $bik = $rqBank['value'];
                                }
                            }
                            if ($rqBank['code'] == 'comments') {
                                if (!empty($rqBank['value'])) {
                                    $bankOther = $rqBank['value'];
                                }
                            }
                        }
                    }
                }
            }
        }

        if ($currentClientType == 'fiz') {
            $client_rq = $companyName . "\n" . "\n"
                . "ИНН: " . $inn . "\n"
                . 'Адрес: ' . $address . "\n"
                . "Документ: " . $fizDocument . "\n"
                . 'Телефон.: ' . $phone . "\n"
                . 'E-mail: ' . $email . "\n";
        } else {
            $client_rq = $companyName . "\n" . "\n"
                . 'Адрес: ' . $address . "\n"
                . "ИНН: " . $inn . "\n"
                . "Р/с: " . $rs . "\n"
                . "К/с: " . $ks . "\n"
                . (!empty($bik) ? "БИК: " . $bik . "\n" : '')
                . (!empty($bankOther) ? $bankOther . "\n" : '')
                . "Банк: " . $bank . "\n"
                . 'Телефон.: ' . $phone . "\n"
                . 'E-mail: ' . $email . "\n";
        }
        return ['client_rq' => $client_rq, 'client_adress' => $client_adress];
    }

    protected function getContractHeaderText(
        $contractType,
        $clientType,
        // $clientCompanyFullName,
        // $clientCompanyDirectorNameCase,   //директор | ип | ''
        // $clientCompanyDirectorPositionCase,
        // $clientCompanyBased,
        // $providerCompanyFullName,
        // $providerCompanyDirectorNameCase,
        // $providerCompanyDirectorPositionCase,
        // $providerCompanyBased,
        $clientRq,
        $providerRq
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
        $providerType = $providerRq['type'];

        $providerCompanyFullName = $providerRq['fullname'];



        $providerCompanyDirectorName = $providerRq['director'];
        $providerCompanyDirectorPosition = $providerRq['position'];
        $providerCompanyDirectorNameCase = inflectName($providerCompanyDirectorName, 'родительный');
        $providerCompanyDirectorPositionCase = NounDeclension::getCase($providerCompanyDirectorPosition, Cases::GENITIVE); // Используйте DATIVE для дательного падежа

        $providerCompanyBased = $providerRq['based'];


        $clientCompanyFullName = ' __________________________________________________________ ';
        $clientCompanyDirectorNameCase = '____________________________________________________';
        $clientCompanyDirectorPositionCase = '______________________________________________';
        $clientCompanyBased = 'Устава';

        foreach ($clientRq['fields'] as $rqItem) {

            if (!empty($rqItem['value'])) {


                if ($rqItem['code'] === 'fullname') {
                    if ($clientType == 'ip' || $clientType == 'org' || $clientType == 'org_state') {
                        $clientCompanyFullName = $rqItem['value'];
                    }
                } else if ($rqItem['code'] === 'personName') {
                    if ($clientType == 'fiz') {

                        $clientCompanyFullName = $rqItem['value'];
                    }
                } else  if ($rqItem['code'] === 'position_case') {
                    $clientCompanyDirectorPositionCase = $rqItem['value'];
                } else  if ($rqItem['code'] === 'director_case') {
                    $clientCompanyDirectorNameCase = $rqItem['value'];
                } else  if ($rqItem['code'] === 'based') {
                    $clientCompanyBased = $rqItem['value'];
                }
            }
        }

        $headerText = $providerCompanyFullName . ' , официальный партнер компании "Гарант",
        именуемый в дальнейшем "' . $providerRole;

        if ($providerType == 'org' ||  $providerType == 'org_state') {
            $headerText .=  ', в лице ' . $providerCompanyDirectorPositionCase . ' ' . $providerCompanyDirectorNameCase .
                ', действующего(-ей) на основании ' . $providerCompanyBased;
        }


        $headerText .= ' с одной стороны и ' . $clientCompanyFullName . ',
        именуемое(-ый) в дальнейшем "' . $clientRole;
        if ($clientType == 'org' || $clientType == 'org_state') {
            $headerText .=   ', в лице ' . $clientCompanyDirectorPositionCase . ' ' . $clientCompanyDirectorNameCase . ', действующего(-ей) на основании '
                . $clientCompanyBased;
        }

        if ($clientType == 'ip') {
            $headerText .=  ', действующего(-ей) на основании '
                . $clientCompanyBased;
        }


        $headerText .= ' с другой стороны, заключили настоящий Договор о нижеследующем:';


        return $headerText;
    }


    protected function getRoles(
        $contractType

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
        $result = [
            'provider' => $providerRole,
            'client' => $clientRole,
        ];
        return $result;
    }

    protected function getDocumentNumber() {}
    public function getProducts($arows, $contractName, $isProduct, $contractCoefficient, $clientType)
    {
        $contractFullName = $contractName;
        if ($isProduct) {
            $contractFullName = $contractFullName . ' длительность ' . $contractCoefficient . ' мес. ';
        }

        $products = [];
        foreach ($arows as $key =>  $row) {
            if ($clientType == 'org_state') {
                $productQuantity = 1;
            } else {
                $productQuantity = $row->price->quantity;
            }
            $product = [
                'productNumber' => $key + 1,
                'productName' => $contractFullName . '(' . $row->name . ')',
                'productQuantity' => $productQuantity,
                'productMeasure' => $row->price->measure->name,
                'productPrice' => $row->price->current,
                'productSum' => $row->price->sum,
            ];
            array_push($products, $product);
        }

        return $products;
    }
    public function getSupplyProducts($arows, $contractName, $isProduct, $contractCoefficient, $clientType)
    {
        $contractFullName = $contractName;
        if ($isProduct) {
            $contractFullName = $contractFullName . ' длительность ' . $contractCoefficient . ' мес. ';
        }

        $products = [];
        foreach ($arows as $key =>  $row) {

            $productQuantity = $row['price']['quantity'];
            $productContractCoefficient = $row['product']['contractCoefficient'];
            $quantity = $productQuantity * $productContractCoefficient;
            $complect_sup = '';
            if ($row['productType'] == 'garant') {
                $complect_sup = $row['currentSupply']['name'];
            }

            $productPrice = number_format($row['price']['current'], 2, '.', '');
            $productSum = number_format($row['price']['sum'], 2, '.', '');
            $productPriceDefault = number_format($row['price']['default'], 2, '.', '');
            $product = [
                'productNumber' => $key + 1,
                'productName' => $contractFullName . '(' . $row['name'] . ')',
                'productQuantity' => $productQuantity,
                'productMeasure' => $row['price']['measure']['name'],
                'productPrice' => $productPrice,
                'productSum' =>  $productSum,
                'complect_sup' => $complect_sup,
                'complectName' => $row['name'],
                'productPriceDefault' => $productPriceDefault

            ];
            array_push($products, $product);
        }

        return $products;
    }
    public function getTotal($total, $clientType)
    {
        $contractSum = $total['price']['sum'];
        $contractSum = number_format($contractSum, 2, '.', ''); // "8008.00"
        $totalSumMonth = $total['price']['current'];
        $totalSumMonth = number_format($totalSumMonth, 2, '.', ''); // "8008.00"

        $productQuantity = $total['price']['quantity'];
        $productContractCoefficient = $total['product']['contractCoefficient'];
        $totalQuantity = $productQuantity * $productContractCoefficient;


        $totalQuantityMonth = FileController::getMonthTitleAccusative($totalQuantity);
        $totalQuantityString = $totalQuantityMonth;

        $moneySpeller = new MoneySpeller();


        // Преобразуем сумму в строку
        $contractSumString = $moneySpeller->spell($contractSum, 'RUB');
        $contractSumString = '(' .        $contractSumString . ')';

        $totalSumMonthString = $moneySpeller->spell($totalSumMonth, 'RUB');

        $totalSumMonthString =  '(' .        $totalSumMonthString . ')';


        $total_month_sum = $totalSumMonth;
        $total_month_sum_string = $totalSumMonthString;

        $total_measure = $total['price']['measure']['name'];
        if ($clientType == 'org_state') {
            $total_prepayment_quantity = '1';
            $total_prepayment_quantity_string = '1 месяц';
            $total_prepayment_sum = $totalSumMonth;
            $total_prepayment_sum_string = $totalSumMonthString;
            $total_quantity = $totalQuantity;
            $total_quantity_string = $totalQuantityString;
            $contract_total_sum = $contractSum;
            $contract_total_sum_string = $contractSumString;
        } else {
            $total_prepayment_quantity = $totalQuantity;
            $total_prepayment_quantity_string = $totalQuantityString;
            $total_prepayment_sum = $contractSum;
            $total_prepayment_sum_string = $contractSumString;
            $contract_total_sum = $contractSum; //для коммерсов надо будет вычислить из суммы за весь период обслуживания
            $contract_total_sum_string = $contractSumString; //для коммерсов надо будет вычислить из суммы за весь период обслуживания
            // $total_quantity = ''; //для коммерсов надо будет вычислить из суммы за весь период обслуживания
            // $total_quantity_string = ''; //для коммерсов надо будет вычислить из суммы за весь период обслуживания
            $total_quantity = $totalQuantity;
            $total_quantity_string = $totalQuantityString;
        }
        $result = [
            'total_product_name' => $total['name'],
            'total_prepayment_quantity' => $total_prepayment_quantity,
            'total_prepayment_quantity_string' => $total_prepayment_quantity_string,
            'total_prepayment_sum' => $total_prepayment_sum,
            'total_prepayment_sum_string' => $total_prepayment_sum_string,
            'contract_total_sum' => $contract_total_sum, // уже использовалось'
            'contract_total_sum_string' => $contract_total_sum_string, // уже использовалось'
            'total_quantity' => $total_quantity, //всего месяцев действие договора
            'total_quantity_string' => $total_quantity_string, //всего месяцев действие договора
            'total_month_sum' => $total_month_sum, // сумма в месяц
            'total_month_sum_string' => $total_month_sum_string,
            'total_measure' => $total_measure

        ];

        return $result;
    }

    public function getSupplyTotal($total, $clientType)
    {
        $productQuantity = $total['price']['quantity'];
        $productContractCoefficient = $total['product']['contractCoefficient'];
        $totalQuantity = $productQuantity * $productContractCoefficient;



        $contractSum = $total['price']['sum'];
        $contractSum = number_format($contractSum, 2, '.', ''); // "8008.00"
        $totalSumCurrent = $total['price']['current'];
        $totalSumMonth = round($totalSumCurrent / $productContractCoefficient, 2);
        $totalSumMonth = number_format($totalSumMonth, 2, '.', ''); // "8008.00"



        $totalQuantityMonth = FileController::getMonthTitleAccusative($totalQuantity);
        $totalQuantityString = $totalQuantityMonth;

        $moneySpeller = new MoneySpeller();


        // Преобразуем сумму в строку
        $contractSumString = $moneySpeller->spell($contractSum, 'RUB');
        $contractSumString = '(' .        $contractSumString . ')';

        $totalSumMonthString = $moneySpeller->spell($totalSumMonth, 'RUB');

        $totalSumMonthString =  '(' .        $totalSumMonthString . ')';


        $total_month_sum = $totalSumMonth;
        $total_month_sum_string = $totalSumMonthString;

        $total_measure = $total['price']['measure']['name'];
        // if ($clientType == 'org_state') {
        //     $total_prepayment_quantity = '1';
        //     $total_prepayment_quantity_string = '1 месяц';
        //     $total_prepayment_sum = $totalSumMonth;
        //     $total_prepayment_sum_string = $totalSumMonthString;
        //     $total_quantity = $totalQuantity;
        //     $total_quantity_string = $totalQuantityString;
        //     $contract_total_sum = $contractSum;
        //     $contract_total_sum_string = $contractSumString;
        // } else {
        $total_prepayment_quantity = $totalQuantity;
        $total_prepayment_quantity_string = $totalQuantityString;
        $total_prepayment_sum = $contractSum;
        $total_prepayment_sum_string = $contractSumString;
        $contract_total_sum = $contractSum; //для коммерсов надо будет вычислить из суммы за весь период обслуживания
        $contract_total_sum_string = $contractSumString; //для коммерсов надо будет вычислить из суммы за весь период обслуживания
        // $total_quantity = ''; //для коммерсов надо будет вычислить из суммы за весь период обслуживания
        // $total_quantity_string = ''; //для коммерсов надо будет вычислить из суммы за весь период обслуживания
        $total_quantity = $totalQuantity;
        $total_quantity_string = $totalQuantityString;
        // }
        $result = [
            'total_product_name' => $total['name'],
            'total_supply_name' => $total['supply']['name'],
            'total_prepayment_quantity' => $total_prepayment_quantity,
            'total_prepayment_quantity_string' => $total_prepayment_quantity_string,
            'total_prepayment_sum' => $total_prepayment_sum,
            'total_prepayment_sum_string' => $total_prepayment_sum_string,
            'contract_total_sum' => $contract_total_sum, // уже использовалось'
            'contract_total_sum_string' => $contract_total_sum_string, // уже использовалось'
            'total_quantity' => $total_quantity, //всего месяцев действие договора
            'total_quantity_string' => $total_quantity_string, //всего месяцев действие договора
            'total_month_sum' => $total_month_sum, // сумма в месяц
            'total_month_sum_string' => $total_month_sum_string,
            'total_measure' => $total_measure

        ];

        return $result;
    }

    protected function getSpecificationCDatareate(
        $domain,
        $specification
    ) {
        $targetKeys = [
            'specification_ibig',
            'specification_ismall',
            'specification_iblocks',
            'specification_ers',
            'specification_ers_packets',
            'specification_ers_in_packets',
            'specification_ifree',
            'specification_lt_free',
            'specification_lt_free_services',
            'specification_lt_packet',
            'specification_lt_services',
            'specification_services',


            'specification_supply', //вид размещения
            'specification_supply_comment', //Примечание Вид Размещения
            'specification_distributive' => '', //носители
            'specification_distributive_comment' => '', //Примечание Носители
            'specification_dway', //  предоставляются следующим способом
            'specification_service_period', //Периодичность предоставления услуг
            'specification_supply_quantity', /// Количество логинов и паролей
            'specification_key_period', // Длительность работы ключа
            'specification_email', //Email для интернет-версии
            // 'specification_email_comment', // Email прмечание

            'abon_long', // Срок действия абонемента

            'lic_long' => '', //Срок действия лицензии, количество лицензий
            'contract_internet_email' => '',

            "specification_pk" => '', // правовой консалтинг ...
            "specification_pk_comment1" => '', // "Выбранный комплект дополняется информационным блоком «Баз
            "specification_pk_comment" => '', // "* Информационный блок «База знаний службы Правового консалтинга» с
            "complect_name" => ''

        ];


        $infoblocks = '';
        $lt = '';
        $supplyContract = '';
        $licLong = '';
        $loginsQuantity = '';
        $contractInternetEmail = '_____________________________________________________';
        $supplyComment = '';
        $specification_pk = '';
        $specification_pk_comment1 = '';
        $specification_pk_comment = '';
        $specification_pk_comment = '';
        $complect_name = '';
        $specification_email_comment = '';
        $specification_services = '';
        $specification_distributive = '';
        $specification_distributive_comment = '';
        $specification_dway = '';
        $specification_dway_comment = '';

        foreach ($specification as $key => $value) {
            if ($domain == 'april-garant.bitrix24.ru') {
                if (
                    $value['code'] === 'specification_ibig' ||
                    $value['code'] === 'specification_ismall'



                ) {
                    $infoblocks .= $value['value'] . "\n";
                }
            } else {
                if (
                    $value['code'] === 'specification_iblocks'



                ) {
                    $infoblocks .= $value['value'] . "\n";
                }
            }
            if (
                $value['code'] === 'complect_name'
            ) {
                if ($domain !== 'gsr.bitrix24.ru') {

                    $complect_name = "\n" . $value['value'] . " \n";
                } else {
                    $complect_name =  $value['value'];
                }
            }


            if (
                // $value['code'] === 'specification_ibig' ||
                // $value['code'] === 'specification_ismall' ||
                $value['code'] === 'specification_ers' ||
                $value['code'] === 'specification_ers_packets' ||
                $value['code'] === 'specification_ers_in_packets' ||
                $value['code'] === 'specification_ifree'


            ) {
                $infoblocks .= $value['value'] . "\n";
            }


            if (

                $value['code'] === 'specification_services'


            ) {
                $specification_services .= $value['value'] . "\n";
            }

            if (
                $value['code'] === 'specification_lt_free' ||
                $value['code'] === 'specification_lt_free_services' ||
                $value['code'] === 'specification_lt_packet' ||
                $value['code'] === 'specification_lt_services'
            ) {
                $lt .= "\n" . $value['value'] . "\n";
            }

            if (
                $value['code'] === 'specification_supply'
            ) {
                $supplyContract .= $value['value'] . "\n";
            }

            if (

                $value['code'] === 'specification_supply_comment'
            ) {
                $supplyComment .= $value['value'] . "\n";
            }


            if (
                $value['code'] === 'lic_long'
            ) {
                $licLong = $value['value'];
            }

            if (
                $value['code'] === 'specification_supply_quantity'
            ) {
                $loginsQuantity = $value['value'];
            }

            if (
                $value['code'] === 'contract_internet_email'
            ) {
                $contractInternetEmail = $value['value'];
            }
            if (
                $value['code'] === 'contract_internet_email'
            ) {
                if (!empty($value['value'])) {
                    $client_assigned_fio = $value['value'];
                }
            }


            if (
                $value['code'] === 'specification_pk'
            ) {
                $specification_pk = 'Правовая поддержка: ' . $value['value'];
            }
            if (
                $value['code'] === 'specification_pk_comment1'
            ) {
                $specification_pk_comment1 = $value['value'];
            }
            if (
                $value['code'] === 'specification_pk_comment'
            ) {
                $specification_pk_comment = $value['value'];
            }
            if (
                $value['code'] === 'specification_email_comment'
            ) {
                $specification_email_comment = $value['value'];
            }

            if (
                $value['code'] === 'specification_distributive'
            ) {
                $specification_distributive = $value['value'];
            }
            if (
                $value['code'] === 'specification_distributive_comment'
            ) {
                $specification_distributive_comment = $value['value'];
            }
            if (
                $value['code'] === 'specification_dway'
            ) {
                $specification_dway = $value['value'];
            }
            if (
                $value['code'] === 'specification_dway_comment'
            ) {
                $specification_dway_comment = $value['value'];
            }
        }


        $specificationData = [
            'complect_name' => $complect_name,
            'infoblocks' => $infoblocks,
            'specification_services' => $specification_services,
            'legal_techs' => $lt,
            'supply_contract' => $supplyContract,
            'supply_comment_1' => $supplyComment,
            'logins_quantity' => $loginsQuantity,
            'lic_long' => $licLong,
            'contract_internet_email' => $contractInternetEmail,
            // 'client_assigned_fio' => $client_assigned_fio
            'specification_email_comment' => $specification_email_comment,
            "specification_pk" =>  $specification_pk, // правовой консалтинг ...
            "specification_pk_comment1" => $specification_pk_comment1, // "Выбранный комплект дополняется информационным блоком «Баз
            "specification_pk_comment" => $specification_pk_comment, // "* Информационный блок «База знаний службы Правового консалтинга» с
            "specification_distributive" => $specification_distributive,
            "specification_distributive_comment" => $specification_distributive_comment,
            "specification_dway" => $specification_dway,
            "specification_dway_comment" => $specification_dway_comment

        ];

        return $specificationData;
    }
}
