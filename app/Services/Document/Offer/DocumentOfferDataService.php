<?php

namespace App\Services\Document\Offer;


use morphos\Gender;
use function morphos\Russian\detectGender;

class DocumentOfferDataService
{

    protected  $domain;
    protected  $providerRq;
    protected  $isTwoLogo;
    protected  $manager;

    protected  $documentNumber;
    protected  $fields;
    protected  $recipient;
    protected $letterText;

    public function __construct(
        $domain,
        $providerRq,
        $isTwoLogo,
        $manager,
        $documentNumber,
        $fields, //template fields
        $recipient
    ) {
        $this->providerRq =  $providerRq;
        $this->isTwoLogo =  $isTwoLogo;
        $this->manager =  $manager;
        $this->domain =  $domain;
        $this->documentNumber =  $documentNumber;
        $this->fields =  $fields;
        $this->recipient =  $recipient;
        $this->getLetterText($fields);
    }

    public function getOfferData(){
        return [
            'header' => $this->getHeaderData(),
            'doubleHeader' => $this->getDoubleHeaderData(),
            'footer' => $this->getFooterData(),
            'letter' => $this->getLetterData(),

        ];
    }
    protected function getHeaderData()
    {
        $providerRq = $this->providerRq;
        $headerData = [
            'isTwoLogo' => $this->isTwoLogo,
            'rq' => '',
            'logo_1' => null,
            'logo_2' => null,
        ];
        $rq = '';
        if (!$this->isTwoLogo) {


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

    protected function getDoubleHeaderData()
    {
        $providerRq = $this->providerRq;
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
    protected function getFooterData()
    {
        $manager = $this->manager;
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
            $managerEmail = '';
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



            $email = '';
            if (isset($manager['EMAIL'])) {
                if (!empty($manager['EMAIL'])) {
                    $managerEmail = $manager['EMAIL'];
                }
            }

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
    protected function getLetterData()
    {
        $isLargeLetterText = $this->getIsLargeLetterText();
        $documentNumber = $this->documentNumber;
        $fields = $this->fields;
        $recipient = $this->recipient;
        $domain = $this->domain;

      


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



        if (!empty($recipient)) {
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
        $gender = count($parts) === 3 ? detectGender($parts[2]) : null;
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


    protected function getIsLargeLetterText()
    {
        $isLarge = false;

        if ($this->domain == 'april-garant.bitrix24.ru' || $this->domain == 'gsr.bitrix24.ru') {
            $isLarge = true;
        }
        return $isLarge;
    }
    protected function getLetterText($fields)
    {
        $this->letterText = '';
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
        $this->letterText = $letterText;
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
