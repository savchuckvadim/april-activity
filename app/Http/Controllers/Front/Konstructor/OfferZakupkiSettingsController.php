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
                    ->where('is_default', true)
                    ->first();

                if (!$settings) {
                    $settings = OfferZakupkiSettings::where('domain', $domain)
                        ->first();
                }
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
                    'type' => 'string',
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
                    'name' => 'Должность руководителя организации',
                    'placeholder' => 'Директор',
                    'code' => 'provider1_position',
                    'value' => $settings->provider1_position,
                    'type' => 'string',
                    'isRequired' => true,
                ],
                [
                    'id' => 8,
                    'name' => 'Текст сопроводительного письма',
                    'placeholder' => 'Текст сопроводительного письма. Используйте {{period}} - для отображения даты действия договора и {{product_name}} для отображения названия комплекта в тексте письма',
                    'code' => 'provider1_letter_text',
                    'value' => $settings->provider1_letter_text,
                    'type' => 'text',
                    'isRequired' => false,
                ],
                [
                    'id' => 9,
                    'name' => 'Разница в цене',
                    'placeholder' => '',
                    'code' => 'provider1_price_coefficient',
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
                    'type' => 'string',
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
                    'name' => 'Должность руководителя организации',
                    'placeholder' => 'Директор',
                    'code' => 'provider2_position',
                    'value' => $settings->provider2_position,
                    'type' => 'string',
                    'isRequired' => true,
                ],
                [
                    'id' => 8,
                    'name' => 'Текст сопроводительного письма',
                    'placeholder' => 'Текст сопроводительного письма. Используйте {{period}} - для отображения даты действия договора и {{product_name}} для отображения названия комплекта в тексте письма',
                    'code' => 'provider2_letter_text',
                    'value' => $settings->provider2_letter_text,
                    'type' => 'text',
                    'isRequired' => false,
                ],
                [
                    'id' => 9,
                    'name' => 'Разница в цене',
                    'placeholder' => '',
                    'code' => 'provider2_price_coefficient',
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

    public function store(Request $request)
    {
        try {
            $data = $request->all();

            // Validate required fields
            $request->validate([
                'domain' => 'required|string',
                'bxUserId' => 'required|integer',
                'name' => 'required|string',
                'portal_id' => 'required|exists:portals,id',

                // Provider 1 fields
                // 'provider1_id' => 'nullable|integer',
                // 'provider1_name' => 'nullable|string|max:255',
                'provider1_shortname' => 'nullable|string|max:255',
                'provider1_address' => 'nullable|string',
                'provider1_phone' => 'nullable|string|max:20',
                'provider1_email' => 'nullable|string|max:255',
                'provider1_letter_text' => 'nullable|string',
                'provider1_inn' => 'nullable|string|max:12',
                'provider1_director' => 'nullable|string|max:255',
                'provider1_position' => 'nullable|string|max:255',

                // 'provider1_logo' => 'nullable|string|max:255',
                // 'provider1_stamp' => 'nullable|string|max:255',
                // 'provider1_signature' => 'nullable|string|max:255',
                'provider1_price_coefficient' => 'nullable|numeric|min:0',
                // 'provider1_price_settings' => 'nullable|string',

                // Provider 2 fields
                // 'provider2_id' => 'nullable|integer',
                // 'provider2_name' => 'nullable|string|max:255',
                'provider2_shortname' => 'nullable|string|max:255',
                'provider2_address' => 'nullable|string',
                'provider2_phone' => 'nullable|string|max:20',
                'provider2_email' => 'nullable|string|max:255',
                'provider2_letter_text' => 'nullable|string',
                'provider2_inn' => 'nullable|string|max:12',
                'provider2_director' => 'nullable|string|max:255',
                'provider2_position' => 'nullable|string|max:255',
                // 'provider2_logo' => 'nullable|string|max:255',
                // 'provider2_stamp' => 'nullable|string|max:255',
                // 'provider2_signature' => 'nullable|string|max:255',
                'provider2_price_coefficient' => 'nullable|numeric|min:0',
                // 'provider2_price_settings' => 'nullable|string',

                // Settings flags
                'is_default' => 'nullable|boolean',
                // 'is_current' => 'nullable|boolean',
                // 'is_one_document' => 'nullable|boolean',
            ]);

            // $isDefault = $data['is_default'] ?? false;
            // if ($isDefault) {
            //     $settings =  OfferZakupkiSettings::where('domain', $data['domain'])
            //         ->where('is_default', $data['is_default'])
            //         ->update(['is_default' => false]);
            // }
            // if (!$settings) {
                $settings = new OfferZakupkiSettings($data);
                $settings->save();
            // }

            return APIController::getSuccess([
                'message' => 'Settings created successfully',
                'settings' => $settings
            ]);
        } catch (\Throwable $th) {
            $errorMessages = [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ];

            return APIController::getError('Failed to create zakupki settings', [
                'error' => $errorMessages,
                'data' => $request->all()
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $data = $request->all();

            // Find the settings
            $settings = OfferZakupkiSettings::findOrFail($id);

            // Validate fields
            $request->validate([
                'domain' => 'sometimes|required|string',
                'bxUserId' => 'sometimes|required|integer',
                'name' => 'sometimes|required|string',
                'portal_id' => 'sometimes|required|exists:portals,id',

                // Provider 1 fields
                'provider1_id' => 'nullable|integer',
                'provider1_name' => 'nullable|string|max:255',
                'provider1_shortname' => 'nullable|string|max:255',
                'provider1_address' => 'nullable|string',
                'provider1_phone' => 'nullable|string|max:20',
                'provider1_email' => 'nullable|string|max:255',
                'provider1_letter_text' => 'nullable|string',
                'provider1_inn' => 'nullable|string|max:12',
                'provider1_director' => 'nullable|string|max:255',
                'provider1_position' => 'nullable|string|max:255',
                // 'provider1_logo' => 'nullable|string|max:255',
                // 'provider1_stamp' => 'nullable|string|max:255',
                // 'provider1_signature' => 'nullable|string|max:255',
                'provider1_price_coefficient' => 'nullable|numeric|min:0',
                // 'provider1_price_settings' => 'nullable|string',

                // Provider 2 fields
                'provider2_id' => 'nullable|integer',
                'provider2_name' => 'nullable|string|max:255',
                'provider2_shortname' => 'nullable|string|max:255',
                'provider2_address' => 'nullable|string',
                'provider2_phone' => 'nullable|string|max:20',
                'provider2_email' => 'nullable|string|max:255',
                'provider2_letter_text' => 'nullable|string',
                'provider2_inn' => 'nullable|string|max:12',
                'provider2_director' => 'nullable|string|max:255',
                'provider2_position' => 'nullable|string|max:255',
                // 'provider2_logo' => 'nullable|string|max:255',
                // 'provider2_stamp' => 'nullable|string|max:255',
                // 'provider2_signature' => 'nullable|string|max:255',
                'provider2_price_coefficient' => 'nullable|numeric|min:0',
                // 'provider2_price_settings' => 'nullable|string',

                // Settings flags
                'is_default' => 'nullable|boolean',
                // 'is_current' => 'nullable|boolean',
                // 'is_one_document' => 'nullable|boolean',
            ]);

            // Update settings
            $settings->update($data);

            return APIController::getSuccess([
                'message' => 'Settings updated successfully',
                'settings' => $settings
            ]);
        } catch (\Throwable $th) {
            $errorMessages = [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ];

            return APIController::getError('Failed to update zakupki settings', [
                'error' => $errorMessages,
                'id' => $id,
                'data' => $request->all()
            ]);
        }
    }
    public function updateDefault(Request $request)
    {
        try {
            $data = $request->all();

            // Find the settings


            // Validate fields
            $request->validate([
                'domain' => 'sometimes|required|string',
                'bxUserId' => 'sometimes|required|integer',
                'name' => 'sometimes|required|string',
                'portal_id' => 'sometimes|required|exists:portals,id',

                // Provider 1 fields
                'provider1_id' => 'nullable|integer',
                'provider1_name' => 'nullable|string|max:255',
                'provider1_shortname' => 'nullable|string|max:255',
                'provider1_address' => 'nullable|string',
                'provider1_phone' => 'nullable|string|max:20',
                'provider1_email' => 'nullable|string|max:255',
                'provider1_letter_text' => 'nullable|string',
                'provider1_inn' => 'nullable|string|max:12',
                'provider1_director' => 'nullable|string|max:255',
                'provider1_position' => 'nullable|string|max:255',
                // 'provider1_logo' => 'nullable|string|max:255',
                // 'provider1_stamp' => 'nullable|string|max:255',
                // 'provider1_signature' => 'nullable|string|max:255',
                'provider1_price_coefficient' => 'nullable|numeric|min:0',
                // 'provider1_price_settings' => 'nullable|string',

                // Provider 2 fields
                'provider2_id' => 'nullable|integer',
                'provider2_name' => 'nullable|string|max:255',
                'provider2_shortname' => 'nullable|string|max:255',
                'provider2_address' => 'nullable|string',
                'provider2_phone' => 'nullable|string|max:20',
                'provider2_email' => 'nullable|string|max:255',
                'provider2_letter_text' => 'nullable|string',
                'provider2_inn' => 'nullable|string|max:12',
                'provider2_director' => 'nullable|string|max:255',
                'provider2_position' => 'nullable|string|max:255',
                // 'provider2_logo' => 'nullable|string|max:255',
                // 'provider2_stamp' => 'nullable|string|max:255',
                // 'provider2_signature' => 'nullable|string|max:255',
                'provider2_price_coefficient' => 'nullable|numeric|min:0',
                // 'provider2_price_settings' => 'nullable|string',

                // Settings flags
                // 'is_default' => 'nullable|boolean',
                // 'is_current' => 'nullable|boolean',
                // 'is_one_document' => 'nullable|boolean',
            ]);
            $domain = $data['domain'];
            $data['is_default'] = true;
            $settings = OfferZakupkiSettings::where('domain', $domain)
                ->where('is_default', true)
                ->first();

            // Update settings
            $settings->update($data);

            return APIController::getSuccess([
                'message' => 'Settings updated successfully',
                'settings' => $settings
            ]);
        } catch (\Throwable $th) {
            $errorMessages = [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ];

            return APIController::getError('Failed to update default zakupki settings', [
                'error' => $errorMessages,

                'data' => $request->all()
            ]);
        }
    }
    public function delete($id)
    {
        try {
            // Find the settings
            $settings = OfferZakupkiSettings::findOrFail($id);

            // Check if it's a default setting
            if ($settings->is_default) {
                return APIController::getError('Cannot delete default settings', [
                    'id' => $id
                ]);
            }

            // Delete the settings
            $settings->delete();

            return APIController::getSuccess([
                'message' => 'Settings deleted successfully',
                'id' => $id
            ]);
        } catch (\Throwable $th) {
            $errorMessages = [
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString(),
            ];

            return APIController::getError('Failed to delete zakupki settings', [
                'error' => $errorMessages,
                'id' => $id
            ]);
        }
    }
}
