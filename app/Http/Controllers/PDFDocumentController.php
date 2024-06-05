<?php

namespace App\Http\Controllers;

use App\Jobs\BitrixDealDocumentJob;
use App\Models\Counter;
use App\Models\Infoblock;
use App\Services\BitrixDealDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use morphos\Cases;
use morphos\Gender;
use morphos\Russian\MoneySpeller;
use Ramsey\Uuid\Uuid;
use morphos\Russian\Cases as RussianCases;
use morphos\Russian\TimeSpeller;

use function morphos\Russian\detectGender;
use function morphos\Russian\pluralize;



class PDFDocumentController extends Controller
{
    public function getDocument($data)
    {
        $pdfData = [];
        try {
            if ($data &&  isset($data['template']) && isset($data['infoblocks'])) {
                $template = $data['template'];
                if ($template && isset($template['id'])) {


                    $templateId = $template['id'];
                    $domain = $data['template']['portal'];
                    $dealId = $data['dealId'];
                    $providerRq = $data['provider']['rq'];
                    $isTwoLogo = false;
                    if ($providerRq) {
                        if (isset($providerRq['logos'])) {
                            if (count($providerRq['logos']) > 1) {
                                $isTwoLogo = true;
                            }
                        }
                    }





                    //TODO BIG DESCRIPTION

                    $placement = null;
                    if (isset($data['placement'])) {
                        $placement = $data['placement'];
                    }

                    $userId = null;
                    if (isset($data['userId'])) {
                        $userId = $data['userId'];
                    }


                    //DOCUMENT SETTINGS
                    //infoblocks data
                    $infoblocksOptions = [
                        'description' => $data['infoblocks']['description']['current'],
                        // 'description' => ['id' => 3],
                        'style' => $data['infoblocks']['style']['current']['code'],
                    ];

                    $settings = $data['infoblocks'];
                    $withStamps = true;
                    $withManager = true;


                    if (isset($settings['withStamps'])) {
                        if (isset($settings['withStamps']['current'])) {
                            if (isset($settings['withStamps']['current']['code'])) {
                                if ($settings['withStamps']['current']['code'] === 'yes') {
                                    $withStamps = true;
                                } else if ($settings['withStamps']['current']['code'] === 'no') {
                                    $withStamps = false;
                                }
                            }
                        }
                    } else {
                        if (isset($data['withStamps'])) {
                            $withStamps = $data['withStamps'];
                        }
                    }
                    if (isset($settings['withManager'])) {
                        if (isset($settings['withManager']['current'])) {
                            if (isset($settings['withManager']['current']['code'])) {
                                if ($settings['withManager']['current']['code'] === 'yes') {
                                    $withManager = true;
                                } else if ($settings['withManager']['current']['code'] === 'no') {
                                    $withManager = false;
                                }
                            }
                        }
                    }
                    $salePhrase = '';

                    if (isset($data['salePhrase'])) {
                        $salePhrase = $data['salePhrase'];
                    }

                    $priceFirst = false;
                    if (!empty($data['settings'])) {
                        if (!empty($data['settings']['isPriceFirst'])) {
                            if (!empty($data['settings']['isPriceFirst']['current'])) {
                                if (!empty($data['settings']['isPriceFirst']['current']['id'])) {
                                    $priceFirst = true; //0 - no 1 -yes
                                }
                            }
                        }
                    }

                    $complect = $data['complect'];

                    $region = $data['region'];

                    $complectName = '';

                    foreach ($data['price']['cells']['total'][0]['cells'] as $cell) {

                        if ($cell['code'] === 'name') {
                            $complectName = $cell['value'];
                        }
                    }



                    //price
                    $price = $data['price'];

                    //SORT CELLS
                    // $sortActivePrices = $this->getSortActivePrices($comePrices);
                    // $allPrices =  $sortActivePrices;
                    // $general = $allPrices['general'];
                    // $alternative = $allPrices['alternative'];


                    //manager
                    $manager = $data['manager'];
                    //UF_DEPARTMENT
                    //SECOND_NAME


                    //fields
                    $fields = $data['template']['fields'];
                    $recipient = $data['recipient'];


                    //document number
                    $documentNumber = CounterController::getCount($providerRq['id'], 'offer');

                    //invoice
                    $invoices = [];
                    $invoiceBaseNumber =  preg_replace('/\D/', '', $documentNumber);
                    $invoiceData = $data['invoice'];
                    $invoiceDate = '';

                    if (isset($data['invoiceDate'])) {
                        $invoiceDate = $data['invoiceDate'];
                    }


                    $isGeneralInvoice = false;
                    $isAlternativeInvoices = false;



                    if (isset($invoiceData['one']) && isset($invoiceData['many'])) {
                        $isGeneralInvoice = $invoiceData['one']['isActive'] && $invoiceData['one']['value'];
                        $isAlternativeInvoices = $invoiceData['many']['isActive'] && $invoiceData['many']['value'];
                    }



                    //general
                    $comePrices = $price['cells'];
                    $productsCount = $this->getProductsCount($comePrices);




                    $regions = null;
                    if (isset($data['regions'])) {
                        $regions = $data['regions'];
                    }


                    //document data
                    $headerData  = $this->getHeaderData($providerRq, $isTwoLogo);
                    $doubleHeaderData  = $this->getDoubleHeaderData($providerRq);
                    $footerData  = $this->getFooterData($manager, $domain);
                    $letterData  = $this->getLetterData($documentNumber, $fields, $recipient, $domain);
                    $infoblocksData  = $this->getInfoblocksData($infoblocksOptions, $complect, $complectName, $productsCount, $region, $salePhrase, $withStamps, $priceFirst, $regions);
                    //testing       // $bigDescriptionData  = $this->getBigDescriptionData($complect, $complectName);
                    $bigDescriptionData = [];
                    $pricesData  =   $this->getPricesData($price,  $salePhrase, $infoblocksData['withPrice'], false);
                    $stampsData  =   $this->getStampsData($providerRq, false);
                    // $invoiceData  =   $this->getInvoiceData($invoiceBaseNumber, $providerRq, $recipient, $price);
                    //testing


                    // if (isset($data['isPublic'])) {
                    //     if (!empty($data['isPublic'])) {
                            $documentService = new BitrixDealDocumentService(
                                $domain,
                                $placement,
                                $userId,
                                $providerRq,
                                $documentNumber,
                                $data,
                                $invoiceDate,
                                $headerData,
                                $doubleHeaderData,
                                $footerData,
                                $letterData,
                                $infoblocksData,
                                $bigDescriptionData,
                                $pricesData,
                                $stampsData,
                                $isTwoLogo,
                                $isGeneralInvoice,
                                $isAlternativeInvoices,
                                $dealId,
                                $withStamps,
                                $withManager

                            );
                            $documentService->getDocuments();
                    //         // $documentController = new DocumentController();
                    //         // return $documentController->getDocument($data);
                    //         return APIController::getSuccess(
                    //             $documents

                    //         );
                    //     }
                    // } else {

                    // dispatch(new BitrixDealDocumentJob(
                    //     $domain,
                    //     $placement,
                    //     $userId,
                    //     $providerRq,
                    //     $documentNumber,
                    //     $data,
                    //     $invoiceDate,
                    //     $headerData,
                    //     $doubleHeaderData,
                    //     $footerData,
                    //     $letterData,
                    //     $infoblocksData,
                    //     $bigDescriptionData,
                    //     $pricesData,
                    //     $stampsData,
                    //     $isTwoLogo,
                    //     $isGeneralInvoice,
                    //     $isAlternativeInvoices,
                    //     $dealId,
                    //     $withStamps,
                    //     $withManager
                    // ));

                    // return APIController::getSuccess(['job' => 'get it !']);
                    // }


                    //testing todo props $bigDescriptionData

                }
            }
        } catch (\Throwable $th) {
            Log::channel('telegram')->error('APRIL_ONLINE PDF Cntrlr Service result', ['error messages' => $th->getMessage()]);
            return APIController::getError($th->getMessage(), ['come' => $data, 'pdf' => $pdfData]);
        }
    }
    public function getInvoice($data, $isTwoLogo,  $documentNumber, $isGeneral = true, $alternativeSetId = 0)
    {
        if ($data &&  isset($data['template'])) {
            $template = $data['template'];
            if ($template && isset($template['id'])) {


                $templateId = $template['id'];
                $domain = $data['template']['portal'];
                $dealId = $data['dealId'];
                $providerRq = $data['provider']['rq'];





                //price
                $price = $data['price'];

                //SORT CELLS
                // $sortActivePrices = $this->getSortActivePrices($comePrices);
                // $allPrices =  $sortActivePrices;
                // $general = $allPrices['general'];
                // $alternative = $allPrices['alternative'];


                //manager
                // $manager = $data['manager'];
                //UF_DEPARTMENT
                //SECOND_NAME


                //recipient

                $recipient = $data['recipient'];


                //invoice
                $invoiceBaseNumber =  preg_replace('/\D/', '', $documentNumber);
                if (!$isGeneral) {
                    $invoiceBaseNumber = $invoiceBaseNumber . '-' . $alternativeSetId + 1;
                }


                //general
                $comePrices = $price['cells'];
                $productsCount = $this->getProductsCount($comePrices);



                //document data
                $headerData  = $this->getHeaderData($providerRq, $isTwoLogo);
                $doubleHeaderData  = $this->getDoubleHeaderData($providerRq);
                $stampsData  =   $this->getStampsData($providerRq, true);
                $invoiceData  =   $this->getInvoiceData($invoiceBaseNumber, $providerRq, $recipient, $price, $isGeneral, $alternativeSetId);

                // ГЕНЕРАЦИЯ ДОКУМЕНТА СЧЕТ
                $pdf = Pdf::loadView('pdf.invoice', [
                    'headerData' =>  $headerData,
                    'doubleHeaderData' =>  $doubleHeaderData,
                    'stampsData' => $stampsData,
                    'invoiceData' => $invoiceData,
                ]);



                // // //СОХРАНЕНИЕ ДОКУМЕТА
                $uid = Uuid::uuid4()->toString();
                $shortUid = substr($uid, 0, 4); // Получение первых 4 символов

                $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/' . $data['userId']);
                $invoicePath = $resultPath . '/invoice';

                if (!file_exists($invoicePath)) {
                    mkdir($invoicePath, 0775, true); // Создать каталог с правами доступа
                }

                // Проверить доступность каталога для записи
                if (!is_writable($invoicePath)) {
                    throw new \Exception("Невозможно записать в каталог: $invoicePath");
                }
                $resultFileName = 'Счет-' . $invoiceBaseNumber . '_' . $shortUid . '.pdf';
                $pdf->save($invoicePath . '/' . $resultFileName);
                $link = asset('storage/clients/' . $domain . '/documents/' . $data['userId'] . '/invoice/' . $resultFileName);


                return  $link;
            }
        }
    }


    public function setTimeline($domain, $dealId, $links, $commentText)
    {
        $method = '/crm.timeline.comment.add';
        // $bitrixController = new BitrixController();
        // $resultTex = "<a href=\\" . $commentLink . "\>" . $commentText . "</a>";

        $resultText = '';
        $currentCommentText = $commentText;
        foreach ($links as $key => $commentLink) {
            if ($key) {
                if ($key == 1) {
                    $currentCommentText = 'СЧЕТ-' . $commentText;
                } else {
                    $numb = $key  - 1;
                    $currentCommentText = 'СЧЕТ-' . $commentText . '-' . $numb;
                }
            }
            $resultText = $resultText . "<a href=\"" . htmlspecialchars($commentLink) . "\">" . htmlspecialchars($currentCommentText) . "</a> \n";
        }
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



    protected function getHeaderData($providerRq, $isTwoLogo)
    {

        $headerData = [
            'isTwoLogo' => $isTwoLogo,
            'rq' => '',
            'logo_1' => null,
            'logo_2' => null,
        ];
        $rq = '';
        if (!$isTwoLogo) {


            $pattern = "/общество\s+с\s+ограниченной\s+ответственностью/ui";
            $patternIp = "/индивидуальный\s+предприниматель/ui";

            $shortenedPhrase = preg_replace($pattern, "ООО", $providerRq['fullname']);
            $companyName = preg_replace($patternIp, "ИП", $shortenedPhrase);


            $rq = $companyName;
            if ($providerRq['inn']) {
                $rq = $rq . ', \n ИНН: ' . $providerRq['inn'];
            }
            if ($providerRq['kpp']) {
                $rq = $rq . ', КПП: ' . $providerRq['kpp'];
            }

            $rq = $rq . ', \n ' . $providerRq['primaryAdresss'];
            if ($providerRq['phone']) {
                // Нормализация номера телефона
                $normalizedPhone = preg_replace('/^8/', '7', $providerRq['phone']); // Заменяем первую цифру 8 на 7
                $normalizedPhone = preg_replace('/^7/', '+7', $normalizedPhone); // Заменяем первую цифру 7 на +7
                // $normalizedPhone = preg_replace('/[^\d+]/', '', $normalizedPhone); // Удаляем все недопустимые символы

                $rq = $rq . ', \n' . $normalizedPhone;
            }
            if ($providerRq['email']) {
                $rq = $rq . ', ' . $providerRq['email'];
            }
        } else {


            if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos']) && count($providerRq['logos']) > 1) {
                $fullPath2 = storage_path('app/' .  $providerRq['logos'][1]['path']);
                $headerData['logo_2'] = $fullPath2;
            }
        }


        if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos'])) {
            $fullPath1 = storage_path('app/' .  $providerRq['logos'][0]['path']);
            $headerData['logo_1'] =  $fullPath1;
        }
        $headerData['rq'] = $rq;
        return $headerData;
    }

    protected function getDoubleHeaderData($providerRq)
    {
        $headerData = [
            'first' => '',
            'second' => '',

        ];
        $rq = '';
        $phone = null;
        $email = null;
        $pattern = "/общество\s+с\s+ограниченной\s+ответственностью/ui";
        $shortenedPhrase = preg_replace($pattern, "ООО", $providerRq['fullname']);
        $companyName = preg_replace($pattern, "ООО", $providerRq['fullname']);


        $first = $companyName;
        if ($providerRq['inn']) {
            $first = $first . ', \n  ИНН: ' . $providerRq['inn'];
        }
        if ($providerRq['kpp']) {
            $first = $first . ', КПП: ' . $providerRq['kpp'];
        }
        if ($providerRq['primaryAdresss']) {
            $first = $first . ', \n' . $providerRq['primaryAdresss'];
        }
        if ($providerRq['rs']) {
            $first = $first . ', \n  р/c: ' . $providerRq['rs'];
        }

        $second = '';




        if ($providerRq['phone']) {
            $phone = $providerRq['phone'];
            $second = $phone;
        }

        if ($providerRq['email']) {
            $email = 'e-mail: ' . $providerRq['email'];
        }
        $second = $second . '' . $email;
        $headerData = [
            'first' => $first,
            'second' => $second,
            'phone' =>  $phone,
            'email' =>  $email,

        ];

        return $headerData;
    }
    protected function getFooterData($manager, $domain)
    {
        $footerData = [
            'managerPosition' => '',
            'name' => '',
            'email' => '',
            'phone' => '',

        ];
        if ($manager) {
            $managerPosition = 'Ваш персональный менеджер';
            $workPhone = '';
            $mobilePhone = '';
            $managerName = '';
            $managerLastName = '';

            if (isset($manager['WORK_POSITION'])) {
                if ($manager['WORK_POSITION']) {
                    $managerPosition = $manager['WORK_POSITION'];
                }
            }
            if (isset($manager['NAME'])) {
                if ($manager['NAME']) {
                    $managerName = $manager['NAME'];
                }
            }
            if (isset($manager['LAST_NAME'])) {
                if ($manager['LAST_NAME']) {
                    $managerLastName = $manager['LAST_NAME'];
                }
            }




            $name =  $managerName . ' ' . $managerLastName;


            // if ($domain == 'april-garant.bitrix24.ru') {
            //     $name = '';
            // }



            $managerEmail = $manager['EMAIL'];
            $email = null;

            if ($managerEmail) {
                $email = 'e-mail: ' . $managerEmail;
            }

            if (isset($manager['WORK_PHONE'])) {
                if ($manager['WORK_PHONE']) {
                    $workPhone = $manager['WORK_PHONE'];
                }
            }

            if (isset($manager['PERSONAL_MOBILE'])) {
                if ($manager['PERSONAL_MOBILE']) {
                    $mobilePhone = $manager['PERSONAL_MOBILE'];
                }
            }


            $phone = $workPhone;
            if (!$phone) {
                $phone = $mobilePhone;
            }
            if ($phone) {
                $phone = 'телефон: ' . $phone;
            }
            $footerData = [
                'managerPosition' =>  $managerPosition,
                'name' =>  $name,
                'email' =>  $email,
                'phone' =>  $phone,

            ];
        }

        return $footerData;
    }
    protected function getLetterData($documentNumber, $fields, $recipient, $domain)
    {
        $isLargeLetterText = false;

        if ($domain == 'april-garant.bitrix24.ru') {
            $isLargeLetterText = true;
        }


        $date = $this->getToday();
        $letterData = [
            'documentNumber' => null,
            'documentDate' => null,
            'companyName' => null,
            'inn' => null,
            'positionCase' => null,
            'recipientCase' => null,
            'recipientName' => null,
            'appeal' => null,
            'text' => null,
            'isLargeLetterText' => $isLargeLetterText

        ];




        if ($documentNumber) {
            $letterData['documentNumber'] = 'Исх. № ' . $documentNumber;
            $letterData['documentDate'] = ' от ' . $date;
        }



        if ($recipient) {
            if (isset($recipient['recipientCase'])) {



                if ($recipient['recipientCase']) {
                    $this->shortenNameWithCase($recipient['recipientCase']);
                    $letterData['recipientCase'] = $this->shortenNameWithCase($recipient['recipientCase']);
                }
            }
            if (isset($recipient['companyName'])) {
                if ($recipient['companyName']) {
                    $letterData['companyName'] = $recipient['companyName'];
                }
            }
            if (isset($recipient['inn'])) {
                if ($recipient['inn']) {
                    $letterData['inn'] = 'ИНН: ' . $recipient['inn'];
                }
            }
            if (isset($recipient['positionCase'])) {
                if ($recipient['positionCase']) {
                    $letterData['positionCase']  = $recipient['positionCase'];
                }
            }
        }

        // $section->addTextBreak(1);
        if (isset($recipient['recipient'])) {
            if ($recipient['recipient']) {
                $name = $recipient['recipient'];
                $letterData['appeal'] = $this->createGreeting($name);
                $letterData['recipientName'] = $name;
            }
        }


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
        $letterData['text'] = $letterText;
        // $parts = preg_split('/<color>|<\/color>/', $letterText);

        // $textRun = $section->addTextRun();

        // $inHighlight = false;
        // foreach ($parts as $part) {
        //     // Разбиваем часть на подстроки по символам переноса строки
        //     $subparts = preg_split("/\r\n|\n|\r/", $part);
        //     // foreach ($subparts as $subpart) {
        //     //     if ($inHighlight) {
        //     //         // Добавление выделенного текста
        //     //         // $textRun->addText($subpart, $corporateletterTextStyle, $styles['paragraphs']['align']['both']);
        //     //     } else {
        //     //         // Добавление обычного текста
        //     //         // $textRun->addText($subpart, $letterTextStyle, $styles['paragraphs']['align']['both']);
        //     //     }
        //     //     // Добавление разрыва строки после каждой подстроки, кроме последней
        //     //     // if ($subpart !== end($subparts)) {
        //     //     //     $textRun->addTextBreak(1);
        //     //     // }
        //     // }
        //     $inHighlight = !$inHighlight;
        // }



        return $letterData;
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




    protected function getInfoblocksData($infoblocksOptions, $complect, $complectName, $productsCount, $region, $salePhrase, $withStamps, $priceFirst, $regions)
    {
        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];
        $itemsPerPage = $this->determineItemsPerPage($descriptionMode, $styleMode);

        $withPrice = false;
        $pages = [];
        $currentPage = [
            'groups' => [],
            'items' => []

        ];
        $currentPageItemsCount = 0;
        $erSubstring = "Пакет Энциклопедий решений";
        $allRegions = [];
        $allRegionsCount = 0;
        if (!empty($regions)) {

            foreach ($regions as $weightType) {
                foreach ($weightType as $rgn) {
                    array_push($allRegions, $rgn);
                }
            }
            $allRegionsCount = count($allRegions);
        }

        // Проверка наличия подстроки в строке без учета регистра

        foreach ($complect as $group) {

            if (stripos($group['groupsName'], $erSubstring) === false) {
                $groupItems = [];
                foreach ($group['value'] as $infoblock) {
                    if (!array_key_exists('code', $infoblock)) {
                        continue;
                    }

                    $infoblockData = Infoblock::where('code', $infoblock['code'])->first();
                    if ($infoblock['code'] == 'reg') {
                        $infoblockData['name'] = $region['infoblock'];

                        // Извлечение названия региона из заголовка
                        $regionName = trim(str_replace("Законодательство", "", $region['infoblock']));

                        // Замена в тексте
                        $infoblockData['descriptionForSale'] = preg_replace("/органов власти регионов/u", "органов $regionName", $infoblockData['descriptionForSale']);
                        $infoblockData['shortDescription'] = preg_replace("/местного законодательства/u", "$regionName", $infoblockData['shortDescription']);

                        if ($allRegionsCount > 1) {
                            $infoblockData['descriptionForSale'] = $infoblockData['descriptionForSale'] . '\\n А также законодательство регионов: \\n';
                            $infoblockData['shortDescription'] = $infoblockData['shortDescription'] . '\\n А также законодательство регионов: \\n';
                            $regFirstCount = 0;
                            foreach ($allRegions as $index => $rgn) {


                                if ($rgn['infoblock'] === $infoblock['title']) {
                                    $regFirstCount += 1;
                                }
                                if ($rgn['infoblock'] !== $infoblock['title']) {
                                    $title = $rgn['title'];


                                    if ($index > $regFirstCount) {
                                        $title = ', ' . $rgn['title'];
                                    }


                                    $infoblockData['descriptionForSale'] = $infoblockData['descriptionForSale'] . $title;
                                    $infoblockData['shortDescription'] = $infoblockData['shortDescription'] . $title;
                                }




                                if ($descriptionMode == 0) {


                                    // Log::channel('console')->info('tst infoblock', ['rgn' => $rgn]);
                                    // Log::channel('console')->info('tst infoblock', ['infoblock' => $infoblock['title']]);

                                    if ($rgn['infoblock'] !== $infoblock['title']) {
                                        // $infoblockDataRegion = Infoblock::where('code', $rgn['code'])->first();
                                        $rgn['name'] = $rgn['infoblock'];
                                        $groupItems[] = $rgn;
                                        array_push($currentPage['items'], $rgn);
                                    }
                                }
                            }
                        }
                    }
                    if ($infoblockData) {
                        $groupItems[] = $infoblockData;
                        array_push($currentPage['items'], $infoblockData);
                    }
                }

                // Распределение элементов группы по страницам
                while (!empty($groupItems)) {
                    $spaceLeft = $itemsPerPage - $currentPageItemsCount; // Сколько элементов помещается на страницу

                    if ($spaceLeft == 0) {
                        // Если на текущей странице нет места, переходим к следующей
                        $pages[] = $currentPage;
                        $currentPage = [
                            'groups' => [],
                            'items' => []

                        ];
                        $currentPageItemsCount = 0;
                        $spaceLeft = $itemsPerPage;
                    }

                    $itemsToAdd = array_splice($groupItems, 0, $spaceLeft); // Элементы, которые поместятся на страницу
                    if (!empty($itemsToAdd)) {
                        // Добавляем часть группы на текущую страницу

                        $currentPage['groups'][] = [
                            'name' => $group['groupsName'],
                            'items' => $itemsToAdd
                        ];
                        $currentPageItemsCount += count($itemsToAdd);
                        if ($group['groupsName'] == 'Нормативно-правовые акты') {
                            if ($group['groupsName'] == 'Нормативно-правовые акты') {
                                // Log::channel('console')->info('ITEMS', ['items' => $itemsToAdd]);
                                if ($allRegionsCount > 10) {

                                    $currentPageItemsCount += 1;
                                }
                                if ($allRegionsCount > 20) {

                                    $currentPageItemsCount += 1;
                                    if ($styleMode == 'table') {
                                        $currentPageItemsCount += 2;
                                    }
                                    //
                                }
                                if ($allRegionsCount > 30) {

                                    $currentPageItemsCount += 1;
                                }
                                if ($allRegionsCount > 30) {

                                    $currentPageItemsCount += 1;
                                }
                            }
                        }
                    }
                }
            }
        }

        // Добавляем последнюю страницу, если она содержит элементы
        if (!empty($currentPage['groups'])) {
            $pages[] = $currentPage;
        }


        $withPrice = $this->getWithPrice($pages, $descriptionMode, $styleMode, $productsCount, $salePhrase, $withStamps, $priceFirst);
        $result = [
            'styleMode' => $styleMode,
            'descriptionMode' => $descriptionMode,
            'pages' => $pages,
            'withPrice' => $withPrice,
            'complectName' => $complectName,


        ];

        return $result;
    }

    protected function getBigDescriptionData($complect, $complectName)
    {



        //     // $pages = [[
        //     //     'previousDescription' => '',
        //     //     'previousItems' => [
        //     //         [
        //     //             'name' => '',
        //     //             'description' => ''
        //     //         ],
        //     //         [
        //     //             'name' => '',
        //     //             'description' => ''
        //     //         ],
        //     //         ...
        //     //     ],
        //     //     'groups' => [[
        //     //         'name' => '',
        //     //         'items' => [
        //     //             [
        //     //                 'name' => '',
        //     //                 'description' => ''
        //     //             ],
        //     //             [
        //     //                 'name' => '',
        //     //                 'description' => ''
        //     //             ],

        //     //             ...

        //     //         ],
        //     //         ...
        //     //         ]
        //     //     ]],
        //     // ];
        $maxWordsPerPage = 500;
        $pages = [];
        $currentPage = ['groups' => []];
        $currentPageWordsCount = 0;

        foreach ($complect as $group) {
            $currentGroup = ['name' => $group['groupsName'], 'items' => []];

            if ($group['groupsName'] !== 'Пакет Энциклопедий решений') {

                foreach ($group['value'] as $infoblock) {

                    if (!array_key_exists('code', $infoblock)) {
                        continue;
                    }


                    $item = Infoblock::where('code', $infoblock['code'])->first();



                    if ($item && $item['description']) {
                        $description = str_replace('\n', "\n", $item['description']);
                        $words = explode(' ', $description);
                        $itemDescriptionParts = [];
                        $itemWordsCount = 0;

                        foreach ($words as $word) {
                            $newLinesCount = substr_count($word, "\n");

                            // Если слово содержит переносы строки, прибавляем 20 за каждый такой символ, иначе просто увеличиваем счетчик на 1
                            $additionalWords = $newLinesCount * 11;
                            $itemWordsCount += 1 + $additionalWords;

                            if (($currentPageWordsCount + $itemWordsCount) <= $maxWordsPerPage) {
                                $itemDescriptionParts[] = $word;
                            } else {
                                // Добавляем инфоблок на текущую страницу и начинаем новую
                                $currentGroup['items'][] = ['name' => $item['name'], 'description' => implode(' ', $itemDescriptionParts)];
                                $currentPage['groups'][] = $currentGroup;
                                $pages[] = $currentPage;
                                $currentPage = ['groups' => []]; // Начинаем новую страницу
                                $currentPageWordsCount = 0; // Сбрасываем счётчик слов для новой страницы
                                $currentGroup = ['name' => $group['groupsName'], 'items' => []]; // Начинаем новую группу
                                $itemDescriptionParts = [$word]; // Начинаем описание с текущего слова
                                $itemWordsCount = 1;
                            }
                        }

                        if (!empty($itemDescriptionParts)) {
                            // Добавляем оставшиеся слова инфоблока в текущую группу
                            $currentGroup['items'][] = ['name' => $item['name'], 'description' => implode(' ', $itemDescriptionParts)];
                            $currentPageWordsCount += $itemWordsCount;
                        }
                    }
                }

                if (!empty($currentGroup['items'])) {
                    // Добавляем последнюю обработанную группу в текущую страницу
                    $currentPage['groups'][] = $currentGroup;
                }
            }
        }

        if (!empty($currentPage['groups'])) {
            // Добавляем последнюю страницу, если она содержит элементы
            $pages[] = $currentPage;
        }

        return [
            'pages' => $pages,
            'complectName' => $complectName
        ];
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
    protected function determineItemsPerPage($descriptionMode, $styleMode)
    {
        $itemsPerPage = 20;

        if ($styleMode === 'list') {

            if ($descriptionMode === 0 || $descriptionMode === 3) {
                $itemsPerPage = 32;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 10;
            } else if ($descriptionMode === 2) {
                $itemsPerPage = 8;
            }
        } else if ($styleMode === 'table') {
            if ($descriptionMode === 0 || $descriptionMode === 3) {
                $itemsPerPage = 60;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 18;
            } else  if ($descriptionMode === 2) {
                $itemsPerPage = 10;
            }
        } else {
            if ($descriptionMode === 0 || $descriptionMode === 3) {
                $itemsPerPage = 24;
            } else if ($descriptionMode === 1) {
                $itemsPerPage = 9;
            } else if ($descriptionMode === 2) {
                $itemsPerPage = 9;
            }
        }

        return $itemsPerPage;
    }




    protected function getPricesData($price,  $salePhrase, $withPrice = false,  $isInvoice = null)
    {
        $isTable = $price['isTable'];
        $comePrices = $price['cells'];
        $total = '';
        $fullTotalstring = '';
        $totalSum = 0;        //SORT CELLS
        $sortActivePrices = $this->getSortActivePrices($comePrices, $isInvoice);
        $allPrices =  $sortActivePrices;


        //IS WITH TOTAL 
        $withTotal = $this->getWithTotal($comePrices);

        $quantityMeasureString = '';
        $quantityString = '';
        $measureString = '';
        $contract = null;
        foreach ($price['cells']['total'][0]['cells'] as $contractCell) {
            if ($contractCell['code'] === 'contract') {
                $contract = $contractCell['value'];
            }
        }
        // if ($withTotal) {
        $foundCell = null;
        foreach ($price['cells']['total'][0]['cells'] as $cell) {
            if ($cell['code'] === 'prepaymentsum') {
                $foundCell = $cell;
            }

            if ($cell['code'] === 'quantity' && $cell['value']) {
                if ($contract['shortName'] !== 'internet' && $contract['shortName'] !== 'proxima') {
                    $quantityString =  TimeSpeller::spellUnit($contract['prepayment'], TimeSpeller::MONTH);
                } else {
                    $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT);

                    // Преобразуем результат в число
                    $quantity = intval($numberString);
                    $quantityString = TimeSpeller::spellUnit($quantity, TimeSpeller::MONTH);
                }
            }

            if ($cell['code'] === 'measure' && $cell['value']) {
                if ($cell['isActive']) {
                    // foreach ($price['cells']['total'][0]['cells'] as $contractCell) {
                    //     if ($contractCell['code'] === 'contract') {
                    //         $measureString = $contractCell['value']['measureFullName'];
                    //     }
                    // }

                }
            }
        }

        $quantityMeasureString = '\n За ' . '<color>' . $quantityString . '</color>';


        if ($foundCell) {
            $totalSum = $foundCell['value'];
            $totalSum = MoneySpeller::spell($totalSum, MoneySpeller::RUBLE, MoneySpeller::SHORT_FORMAT);
            $total = '<color>' . $totalSum . '</color> ';
        }

        $result = MoneySpeller::spell($foundCell['value'], MoneySpeller::RUBLE);
        $firstChar = mb_strtoupper(mb_substr($result, 0, 1, "UTF-8"), "UTF-8");
        $restOfText = mb_substr($result, 1, mb_strlen($result, "UTF-8"), "UTF-8");






        $text = ' (' . $firstChar . $restOfText . ') без НДС';
        $textTotalSum = $text;

        $fullTotalstring = $total . ' ' . $textTotalSum . $quantityMeasureString;
        // }
        // Log::info('getPricesData:', ['price' => [
        //     'isTable' => $isTable,
        //     'isInvoice' => $isInvoice,
        //     'allPrices' => $allPrices,
        //     'withPrice' => $withPrice,
        //     'withTotal' => $withTotal,
        //     'total' => $fullTotalstring

        // ]]);
        return [
            'isTable' => $isTable,
            'isInvoice' => $isInvoice,
            'allPrices' => $allPrices,
            'withPrice' => $withPrice,
            'withTotal' => $withTotal,
            'total' => $fullTotalstring,
            'salePhrase' => $salePhrase

        ];
    }
    protected function getInvoicePricesData($price, $isGeneral = true, $alternativeSetId)
    {
        $isTable = $price['isTable'];
        $comePrices = $price['cells'];
        $total = '';
        $fullTotalstring = '';
        $totalSum = 0;        //SORT CELLS
        $sortActivePrices = $this->getSortActivePrices($comePrices, true);
        $allPrices =  $sortActivePrices['general'];



        $quantityMeasureString = '';
        $quantityString = '';
        $measureString = '';
        $contract = null;


        foreach ($price['cells']['total'][0]['cells'] as $contractCell) {
            if ($contractCell['code'] === 'contract') {
                $contract = $contractCell['value'];
            }
        }

        $foundCell = null;

        foreach ($price['cells']['total'][0]['cells'] as $cell) {
            if ($cell['code'] === 'prepaymentsum') {
                $foundCell = $cell;
            }
        }


        $targetProducts = $sortActivePrices['general'];
        $totalCells = $price['cells']['total'][0]['cells'];


        if (!$isGeneral) {
            foreach ($price['cells']['alternative'][0]['cells'] as $contractCell) {
                if ($contractCell['code'] === 'contract') {
                    $contract = $contractCell['value'];
                }
            }
            $targetProducts = $sortActivePrices['alternative'];
            $totalCells = $price['cells']['alternative'][$alternativeSetId]['cells'];
            $allPrices = [$sortActivePrices['alternative'][$alternativeSetId]];


            foreach ($price['cells']['alternative'][$alternativeSetId]['cells'] as $contractCell) {
                if ($contractCell['code'] === 'contract') {
                    $contract = $contractCell['value'];
                }
            }

            $foundCell = null;

            foreach ($price['cells']['alternative'][$alternativeSetId]['cells'] as $cell) {
                if ($cell['code'] === 'prepaymentsum') {
                    $foundCell = $cell;
                }
            }
        }


        $allProductForInvoice = [];
        foreach ($targetProducts as $productKey => $product) {
            if ($isGeneral) {
                array_push($allProductForInvoice, $product);
            } else {
                if ($productKey == $alternativeSetId) {
                    array_push($allProductForInvoice, $product);
                }
            }
        }




        foreach ($totalCells  as $cell) {

            if ($cell['code'] === 'quantity' && $cell['value']) {
                if ($contract['shortName'] !== 'internet' && $contract['shortName'] !== 'proxima') {

                    $qcount = $contract['prepayment'] * $cell['value'];

                    $quantityString =  TimeSpeller::spellUnit($qcount, TimeSpeller::MONTH);
                } else {
                    $numberString = filter_var($cell['value'], FILTER_SANITIZE_NUMBER_INT);

                    // Преобразуем результат в число
                    $quantity = intval($numberString);
                    $quantityString = TimeSpeller::spellUnit($quantity, TimeSpeller::MONTH);
                }
            }

            if ($cell['code'] === 'measure' && $cell['value']) {
                if ($cell['isActive']) {
                    // foreach ($price['cells']['total'][0]['cells'] as $contractCell) {
                    //     if ($contractCell['code'] === 'contract') {
                    //         $measureString = $contractCell['value']['measureFullName'];
                    //     }
                    // }

                }
            }
        }

        $quantityMeasureString = '\n За ' . '<color>' . $quantityString . '</color>';


        if ($foundCell) {
            $totalSum = $foundCell['value'];
            $totalSum = MoneySpeller::spell($totalSum, MoneySpeller::RUBLE, MoneySpeller::SHORT_FORMAT);
            $total = '<color>' . $totalSum . '</color> ';
        }

        $result = MoneySpeller::spell($foundCell['value'], MoneySpeller::RUBLE);
        $firstChar = mb_strtoupper(mb_substr($result, 0, 1, "UTF-8"), "UTF-8");
        $restOfText = mb_substr($result, 1, mb_strlen($result, "UTF-8"), "UTF-8");






        $text = ' (' . $firstChar . $restOfText . ') без НДС';
        $textTotalSum = $text;

        $fullTotalstring = $total . ' ' . $textTotalSum . $quantityMeasureString;


        return [
            'isTable' => $isTable,
            // 'isInvoice' => $isInvoice,
            'allPrices' => $allPrices,
            // 'withPrice' => $withPrice,
            'withTotal' => true,
            'total' => $fullTotalstring

        ];
    }

    protected function getWithPrice(
        $pages, 
        $descriptionMode, 
        $styleMode, 
        $productsCount, 
        $salePhrase, 
        $withStamps, 
        $priceFirst
        )
    {
        $isWithPrice = false;
        $salePhraseLength = mb_strlen($salePhrase, "UTF-8");
        $entersCount = substr_count($salePhrase, "\n");


        $lastPageItemsCount = 0;
        $lastPage = end($pages);
        if (is_array($lastPage) && isset($lastPage['groups']) && is_array($lastPage['groups'])) {
            $lastPageGroups = $lastPage['groups'];
            foreach ($lastPageGroups as  $lastPageGroup) {
                if (isset($lastPageGroup['items']) && is_array($lastPageGroup['items'])) {
                    $currentGrupItems = $lastPageGroup['items'];
                    $currentGrupItemsCount = count($currentGrupItems);
                    $lastPageItemsCount = $lastPageItemsCount + $currentGrupItemsCount;
                }
            }
        }


        if (!$priceFirst) {
            if ((
                $productsCount < 4 && $salePhraseLength < 150 && $entersCount < 3
                ) || ($productsCount < 3 && $salePhraseLength <= 400 && $entersCount < 4)) {

                if ($styleMode === 'list') {

                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 20) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 5) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 5) {
                            $isWithPrice = true;
                        }
                    }
                } else if ($styleMode === 'table') {

                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 38) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 12) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 9) {
                            $isWithPrice = true;
                        }
                    }
                } else {
                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 10) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 7) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 6) {
                            $isWithPrice = true;
                        }
                    }
                }
            } else if ($productsCount < 5 && ($salePhraseLength < 500 || $entersCount < 4)) {    //если товаров больше или текст описания большой


                if ($styleMode === 'list') {

                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 10) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 3) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 2) {
                            $isWithPrice = true;
                        }
                    }
                } else if ($styleMode === 'table') {

                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 14) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 9) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 4) {
                            $isWithPrice = true;
                        }
                    }
                } else {
                    if ($descriptionMode === 0) {
                        if ($lastPageItemsCount < 7) {
                            $isWithPrice = true;
                        }
                    } else if ($descriptionMode === 1) {
                        if ($lastPageItemsCount < 6) {
                            $isWithPrice = true;
                        }
                    } else {

                        if ($lastPageItemsCount < 3) {
                            $isWithPrice = true;
                        }
                    }
                }
            }
        } else {
            if ($productsCount < 4 || ($productsCount < 5 && $entersCount < 4) || ($productsCount < 6 && $entersCount < 1)) {

                $isWithPrice = true;
            }
        }




        return $isWithPrice;
    }


    protected function getSortActivePrices($allPrices,  $isInvoice)
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
                                if (!$isInvoice) {
                                    $filtredCells = array_filter($product['cells'], function ($prc) {
                                        return $prc['isActive'] == true;
                                    });
                                    usort($filtredCells, function ($a, $b) {
                                        return $a['order'] - $b['order'];
                                    });
                                } else {
                                    $filtredCells = [];
                                    usort($product['cells'], function ($a, $b) {
                                        return $a['order'] - $b['order'];
                                    });
                                    foreach ($product['cells'] as $cell) {
                                        $searchingCell = null;

                                        if ($cell['code'] === 'name') {
                                            $searchingCell = $cell;
                                        }

                                        if ($cell['code'] === 'current') {
                                            $searchingCell = $cell;
                                        }
                                        if ($cell['code'] === 'quantity') {
                                            $cell['name'] = 'Кол-во';
                                            if (preg_match('/\d+/', (string)$cell['value'], $matches)) {
                                                $cell['value'] = $matches[0];
                                            }
                                            $searchingCell = $cell;
                                        }
                                        if ($cell['code'] === 'measure') {
                                            $searchingCell = $cell;
                                        }
                                        if ($cell['code'] === 'prepaymentsum') {
                                            $cell['name'] = 'Сумма';
                                            $searchingCell = $cell;
                                        }

                                        if ($searchingCell) {
                                            array_push($filtredCells, $searchingCell);
                                        }
                                    }
                                    // $filtredCells = array_filter($product['cells'], function ($prc) {
                                    //     return $prc['isActive'] == true || $prc['code'] == 'measure';
                                    // });
                                }
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

        return $isHaveLongPrepayment;
    }


    protected function getWithTotal(
        $allPrices,

    ) {

        $result = false;
        $alternative =  $allPrices['alternative'];
        $general =  $allPrices['general'];



        if (is_array($alternative) && is_array($general)) {

            if (empty($alternative)) { // если нет товаров для сравнения


                if (count($general) > 1) {  //если больше одного основного товара
                    $result = true;
                }

                foreach ($general[0]['cells'] as $key => $cell) {
                    if ($cell['code'] === 'quantity') {            //если есть количество
                        if ($cell['value'] > 1) {
                            $result = true;
                        }
                    }

                    if ($cell['code'] === 'contract') {              //если какой-нибудь навороченный контракт
                        if (isset($cell['value']) && isset($cell['value']['shortName'])) {
                            if ($cell['value']['shortName'] !== 'internet' && $cell['value']['shortName'] !== 'proxima') {
                                $result = true;
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    protected function getProductsCount(
        $allPrices,

    ) {

        $result = 0;
        $alternative =  $allPrices['alternative'];
        $general =  $allPrices['general'];



        if (is_array($alternative) && is_array($general)) {

            if (count($general) > 0) {  //если больше одного основного товара
                $result = $result + count($general);
            }
            if (count($alternative) > 0) {  //если больше одного  товара
                $result = $result + count($alternative);
            }
        }

        return $result;
    }

    protected function getStampsData($providerRq, $isInvoice)
    {
        $stampsData = [
            'position' => '',
            'stamp' => '',
            'signature' => '',
            'signature_accountant' => '',
            'director' => '',
            'accountant' => '',
            'isInvoice' => $isInvoice
        ];
        $stamps = $providerRq['stamps'];
        $signatures = $providerRq['signatures'];

        if (!empty($stamps)) {
            $stampsData['stamp'] = storage_path('app/' . $stamps[0]['path']);
        }
        if (!empty($signatures)) {
            foreach ($signatures as $key => $signature) {
                if ($signature['code'] !== 'signature_accountant') {
                    $stampsData['signature'] = storage_path('app/' . $signature['path']);
                } else {
                    $stampsData['signature_accountant'] = storage_path('app/' . $signature['path']);
                }
            }
        }

        $pattern = "/общество\s+с\s+ограниченной\s+ответственностью/ui";
        $shortenedPhrase = preg_replace($pattern, "ООО", $providerRq['fullname']);
        $companyName = preg_replace($pattern, "ООО", $providerRq['fullname']);


        $stampsData['position'] = $providerRq['position'] . ' ' . $companyName;
        if ($providerRq['type'] == 'ip') {
            $stampsData['position'] = $providerRq['fullname'];
        }



        if ($providerRq['type'] == 'org') {
            $stampsData['director']  = $this->getShortName($providerRq['director']);
        }


        $stampsData['accountant'] = $this->getShortName($providerRq['accountant']);

   


        return $stampsData;
    }


    protected function getShortName($fullName)
    {
        // $fullName = "Иванов Петр Сергеевич";

        // Разделяем полное имя на части
        $parts = explode(' ', $fullName);

        // Проверяем, что имя содержит три части: фамилию, имя, отчество
        if (count($parts) === 3) {
            $shortName = $parts[0] . ' ' . mb_substr($parts[1], 0, 1) . '. ' . mb_substr($parts[2], 0, 1) . '. ';
        } else {
            // Если формат имени отличается, вернуть оригинальное имя или обработать иначе
            $shortName = $fullName;
        }

        return $shortName; // Вывод: Иванов П.С.
    }


    protected function getInvoiceData(
        $invoiceBaseNumber,
        $providerRq,
        $recipient,
        $price,
        $isGeneral,
        $alternativeSetId,

    ) {
        $pricesData  =   $this->getInvoicePricesData($price, $isGeneral, $alternativeSetId);
        $date = $this->getToday();
        $invoiceNumber = 'Счет на оплату № ' . $invoiceBaseNumber . ' от ' .  $date;
        $withQr = false;
        $qr = null;
        if (isset($providerRq['qrs'])) {
            if (count($providerRq['qrs'])) {
                if (isset($providerRq['qrs'][0])) {

                    if (isset($providerRq['qrs'][0]['path'])) {

                        $withQr = true;
                        $qr = $providerRq['qrs'][0]['path'];
                    }
                }
            }
        }
        $req = $providerRq;
        $req['withQr'] = $withQr;
        $req['qr'] = $qr;
        $invoiceData = [
            // 'stampsData' => $stampsData,
            'rq' => $req,

            'main' => [
                'rq' => $providerRq,
                'recipient' => $recipient,
                'number' => $invoiceNumber,
            ],


            'pricesData' => $pricesData,

        ];

        return $invoiceData;
    }

    protected function getToday()
    {
        $months = [
            1 => 'января',
            2 => 'февраля',
            3 => 'марта',
            4 => 'апреля',
            5 => 'мая',
            6 => 'июня',
            7 => 'июля',
            8 => 'августа',
            9 => 'сентября',
            10 => 'октября',
            11 => 'ноября',
            12 => 'декабря'
        ];

        // Получаем текущую дату
        $currentDate = getdate();

        // Форматируем дату
        $formattedDate = $currentDate['mday'] . ' ' . $months[$currentDate['mon']] . ' ' . $currentDate['year'];

        return $formattedDate;
    }
}
