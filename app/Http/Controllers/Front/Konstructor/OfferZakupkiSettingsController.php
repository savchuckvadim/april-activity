<?php

namespace App\Http\Controllers\Front\Konstructor;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Models\OfferZakupkiSettings;
use App\Models\Portal;
use Illuminate\Http\Request;

class OfferZakupkiSettingsController extends Controller
{
    public function get($domain, $userId)
    {
        $favorites = [];
        try {
            $status = [
                'isFromUser' => true,
                'isDefault' => false,
                'isNew' => false,

            ];
            $portal = Portal::where('domain', $domain)->first();
            $settings = OfferZakupkiSettings::where('domain', $domain)
                ->where('bxUserId', $userId)
                ->first();

            if (!$settings) {
                $settings = OfferZakupkiSettings::where('domain', $domain)
                    ->first();
                $status = [
                    'isFromUser' => false,
                    'isDefault' => true,
                    'isNew' => false,

                ];
            }
            if (!$settings) {
                $settings = $this->createDefaultSettings($domain);
                $status = [
                    'isFromUser' => false,
                    'isDefault' => true,
                    'isNew' => true,

                ];
            }
            $form = $this->createSettingsForm($settings);
            $result['settings'] = [
                'id' => $settings->id,
                'domain' => $settings->domain,
                'bxUserId' => $settings->bxUserId,
                'isFromUser' => $status['isFromUser'],
                'isDefault' => $status['isDefault'],
                'isNew' => $status['isNew'],

            ];
            $result['settings']['providers'] = $form;
            // $result = [
            //     'settings' => $result
            // ];
            return APIController::getSuccess($result);
        } catch (\Throwable $th) {
            $errorMessages =  [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ];

            return APIController::getError('zakupki settings get', [
                'error' =>  $errorMessages,
                'domain' => $domain,
                'userId' => $userId,

            ]);
        }
    }
    protected function createSettingsForm($settings)
    {
        $resultSettings = [
            'provider1' => [
                [
                    'id' => 1,
                    'name' => 'Сокращенное наименование',
                    'placeholder' => 'ООО Пирожок',
                    'code' => 'provider1_shortname',
                    'value' => $settings->provider1_shortname,
                    'type' => 'string',
                    'isRequired' => true,
                ],
                [
                    'id' => 2,
                    'name' => 'Адрес',
                    'placeholder' => '35734, г.Лермонтов...',
                    'code' => 'provider1_address',
                    'value' => $settings->provider1_address,
                    'type' => 'text',
                    'isRequired' => true,
                ],
                [
                    'id' => 3,
                    'name' => 'ИНН',
                    'placeholder' => '12345...',
                    'code' => 'provider1_inn',
                    'value' => $settings->provider1_inn,
                    'type' => 'text',
                    'isRequired' => false,
                ],
                [
                    'id' => 4,
                    'name' => 'Телефон',
                    'placeholder' => '+7(962)0027991',
                    'code' => 'provider1_phone',
                    'value' => $settings->provider1_phone,
                    'type' => 'string',
                    'isRequired' => false,
                ],
                [
                    'id' => 5,
                    'name' => 'Email',
                    'placeholder' => 'april-app@mail.ru',
                    'code' => 'provider1_email',
                    'value' => $settings->provider1_email,
                    'type' => 'string',
                    'isRequired' => false,
                ],
                [
                    'id' => 6,
                    'name' => 'Имя руководителя организации',
                    'placeholder' => 'Иванов А.А.',
                    'code' => 'provider1_director',
                    'value' => $settings->provider1_director,
                    'type' => 'string',
                    'isRequired' => true,
                ],
                [
                    'id' => 7,
                    'name' => 'Имя руководителя организации',
                    'placeholder' => 'Директор',
                    'code' => 'provider1_position',
                    'value' => $settings->provider1_position,
                    'type' => 'string',
                    'isRequired' => true,
                ],
                [
                    'id' => 8,
                    'name' => 'Имя руководителя организации',
                    'placeholder' => 'Текст сопроводительного письма. Используйте {{period}} - для отображения даты действия договора и {{product_name}} для отображения названия комплекта в тексте письма',
                    'code' => 'provider1_letter_text',
                    'value' => $settings->provider1_letter_text,
                    'type' => 'string',
                    'isRequired' => false,
                ],
                [
                    'id' => 9,
                    'name' => 'Разница в цене',
                    'placeholder' => '',
                    'code' => 'provider1_price_coefficientм',
                    'value' => $settings->provider1_price_coefficient,
                    'type' => 'string',
                    'isRequired' => false,
                ],

            ],
            'provider2' => [
                [
                    'id' => 1,
                    'name' => 'Сокращенное наименование',
                    'placeholder' => 'ООО Пирожок',
                    'code' => 'provider2_shortname',
                    'value' => $settings->provider2_shortname,
                    'type' => 'string',
                    'isRequired' => true,
                ],
                [
                    'id' => 2,
                    'name' => 'Адрес',
                    'placeholder' => '35734, г.Лермонтов...',
                    'code' => 'provider2_address',
                    'value' => $settings->provider2_address,
                    'type' => 'text',
                    'isRequired' => true,
                ],
                [
                    'id' => 3,
                    'name' => 'ИНН',
                    'placeholder' => '12345...',
                    'code' => 'provider2_inn',
                    'value' => $settings->provider2_inn,
                    'type' => 'text',
                    'isRequired' => false,
                ],
                [
                    'id' => 4,
                    'name' => 'Телефон',
                    'placeholder' => '+7(962)0027991',
                    'code' => 'provider2_phone',
                    'value' => $settings->provider2_phone,
                    'type' => 'string',
                    'isRequired' => false,
                ],
                [
                    'id' => 5,
                    'name' => 'Email',
                    'placeholder' => 'april-app@mail.ru',
                    'code' => 'provider2_email',
                    'value' => $settings->provider2_email,
                    'type' => 'string',
                    'isRequired' => false,
                ],
                [
                    'id' => 6,
                    'name' => 'Имя руководителя организации',
                    'placeholder' => 'Иванов А.А.',
                    'code' => 'provider2_director',
                    'value' => $settings->provider2_director,
                    'type' => 'string',
                    'isRequired' => true,
                ],
                [
                    'id' => 7,
                    'name' => 'Имя руководителя организации',
                    'placeholder' => 'Директор',
                    'code' => 'provider2_position',
                    'value' => $settings->provider2_position,
                    'type' => 'string',
                    'isRequired' => true,
                ],
                [
                    'id' => 8,
                    'name' => 'Имя руководителя организации',
                    'placeholder' => 'Текст сопроводительного письма. Используйте {{period}} - для отображения даты действия договора и {{product_name}} для отображения названия комплекта в тексте письма',
                    'code' => 'provider2_letter_text',
                    'value' => $settings->provider2_letter_text,
                    'type' => 'string',
                    'isRequired' => false,
                ],
                [
                    'id' => 9,
                    'name' => 'Разница в цене',
                    'placeholder' => '',
                    'code' => 'provider2_price_coefficientм',
                    'value' => $settings->provider2_price_coefficient,
                    'type' => 'string',
                    'isRequired' => false,
                ],

            ]
        ];

        return $resultSettings;
    }


    protected function createDefaultSettings($domain)
    {
        $portal = Portal::where('domain', $domain)->first();
        $data = array(
            'portal_id' => $portal->id,
            'domain' => $domain,
            'name' => 'Настройки по умолчанию',
            'provider1_letter_text' => 'Имеем честь предложить заключить контракт на период {{period}} на следующий комплект: ',

            'provider2_letter_text' => 'Имеем честь предложить заключить контракт на период с  {{period}}  года на следующий комплект: ',
            'is_default' => true,

        );
        $settings = new OfferZakupkiSettings($data);
        $settings->save();
        return $settings;
    }
}
