<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use App\Models\BtxCompany;
use App\Models\BtxDeal;
use App\Models\BtxLead;
use App\Models\Portal;
use App\Models\Smart;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstallFieldsController extends Controller
{

    static function setFields(
        $token,
        $entityType,
        $domain,
        $btxSmart = null,
        $portalSmart = null

        // $parentType, //deal company lead smart list
        // $type, //select, date, string,
        // $title, //отображаемое имя
        // $name, //имя в битрикс
        // $bitrixId, //id в bitrix UF_CRM
        // $bitrixCamelId, ////id в bitrix ufCrm
        // $code, ////для доступа из app например comment или actions и будет list->field where code == actions
        // $appOptions
    )

    //TODO fields
    // пербирает fields 
    // создает bitrixfield
    // 'title',
    // 'name',
    // 'code',
    // 'type',
    // 'bitrixId',
    // 'bitrixCamelId',
    // 'entity_id',
    // 'entity_type',
    // 'parent_type', //название типа филда в родительской модели напр list или для deal: offer | calling - к чему относится field
    // принадлежность к crm - создает связь bitrixfield

    //  если есть принадлежность к определенному app надо отобразить в parent_type
    // по entity_type bitrixfield будет связан с определенной моделью например BtxDeal BtxSmart
    // и в итоге будет доступен из Portal для Разных приложений
    // поскольку должна быть возможность  из Portal получить филды модели разной принадлежности
    // так например field НПА принадлежит к приложению конструктор
    // и должен иметь пометку например offer и будет доступен BtxDeal.offerFields()[0].action = crm.deal.add
    // в теории один field может принадлежать к разным app
    // например история работы - хотя он будет доступен только для приложения типа hook list

    //в приходящих перебираемых инициализационных филдах может содержаться информация для app
    // она должна приходить в специальном объекте app options
    // если один field принадлежит к разным app
    // у него могут быть опции для разных приложений
    //  [   option: {
    //         id:0
    //         name: isShowing
    //         app: offerApp
    //         type: boolean
    //         value:false

    //     }]
    // потом у field может быть много options которые будут доступны по типу приложения

    //основные поля BitrixField
    // 'title',
    // 'name',
    // 'code',
    // 'type',
    // 'bitrixId',
    // 'bitrixCamelId',
    // остальное должно лежать раскидано в в app options и содержать app type у каждой option

    // а также должно приходить тип сущности и 
    //как то надо определить id сущности
    // portal->deal->where('group', 'sales')->first()

    {

        $responseData = null;
        try {
            $hook = BitrixController::getHook($domain);
            ini_set('memory_limit', '1G');
            // $fields = [ //string
            //     "FIELD_NAME" => "MY_STRING",
            //     "EDIT_FORM_LABEL" => "Моя строка",
            //     "LIST_COLUMN_LABEL" => "Моя строка",
            //     "USER_TYPE_ID" => "string",
            //     "XML_ID" => "MY_STRING",
            //     "SETTINGS" => ["DEFAULT_VALUE" => "Привет, мир!"]
            // ];
            $portal = Portal::where('domain', $domain)->first();
            $group = 'sales';
            $webhookRestKey = $portal->getHook();
            $portalDeal = $portal->deal();
            $portalLead = $portal->lead();
            $portalCompany = $portal->company();
            // $portalsmart = $portal->smarts->where('bitrixId', $smartId)->first();


            $portalDealFields = [];
            if ((!empty($portalDeal))) {
                $portalDealFields = $portalDeal->fields;
            }

            if (!empty($portalLead)) {
                $portalLeadFields = $portalLead->fields;
            }
            if (!empty($portalCompany)) {
                $portalCompanyFields = $portalCompany->fields;
            }
            // if (!empty($portalsmart)) {
            //     $portalsmart = $portalsmart; //существующие fields в DB привязанные к данному смарт
            //     // Log::channel('telegram')->info("smart", [
            //     //     'portalportalsmartsFields' => $portalsmart,

            //     // ]);
            // }


            $categories = null;
            $url = 'https://script.google.com/macros/s/' . $token . '/exec';
            $response = Http::get($url);

            if ($response->successful()) {
                $googleData = $response->json();
                // Log::channel('telegram')->error("googleData", [
                //     'googleData' => $googleData['fields'],

                // ]);
            } else {
                Log::channel('telegram')->error("Failed to retrieve data from Google Sheets", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return response(['resultCode' => 1, 'message' => 'Error retrieving data'], 500);
            }




            // $webhookRestKey = $portal['portal']['C_REST_WEB_HOOK_URL'];
            $hook = 'https://' . $domain . '/' . $webhookRestKey;


            // $methodSmartInstall = '/crm.type.add.json';
            // $url = $hook . $methodSmartInstall;

            // Проверка на массив
            // if (!empty($googleData['fields'])) {
            $fields = $googleData['fields'];
            // dd($fields);
            // echo memory_get_usage();
            // foreach ($fields as $field) {

            //     $multiple = "N";
            //     $type = $field['type'];
            //     if ($type == 'multiple') {
            //         $type = 'string';
            //         $multiple = "Y";
            //     }


            //     $method = '/crm.deal.userfield.add';
            //     $url = $hook . $method;
            //     $fieldsData = [ //list
            //         "FIELD_NAME" => $field['deal'],
            //         "EDIT_FORM_LABEL" => $field['name'],
            //         "LIST_COLUMN_LABEL" => $field['name'],
            //         "USER_TYPE_ID" => $type,
            //         "LIST" => $field['list'],
            //         "XML_ID" => $field['code'],
            //         "MULTIPLE" => $multiple,
            //         "SETTINGS" => ["LIST_HEIGHT" => 1],
            //         // "ORDER" => 2
            //     ];

            //     $data = [
            //         'fields' => $fieldsData
            //     ];
            //     //                     $response = Http::post($url, $data);
            //     //                     $responseData = BitrixController::getBitrixResponse($response, 'response: deal');
            //     // sleep(2);
            //     //                     $method = '/crm.company.userfield.add';
            //     //                     $fieldsData['FIELD_NAME'] = $field['company'];
            //     //                     $url = $hook . $method;

            //     //                     $response = Http::post($url, $data);
            //     //                     $responseData = BitrixController::getBitrixResponse($response, 'response: company');
            //     //                     sleep(2);
            //     //                     $method = '/crm.lead.userfield.add';
            //     //                     $fieldsData['FIELD_NAME'] = $field['lead'];
            //     //                     $url = $hook . $method;

            //     //                     $response = Http::post($url, $data);
            //     //                     $responseData = BitrixController::getBitrixResponse($response, 'response: lead');
            //     //                     sleep(2);
            //     // if($smartId){
            //     //     $method = '/userfieldconfig.add';
            //     //     $fieldsData['FIELD_NAME'] = $field['smart'];
            //     //     $fieldsData['FIELD_NAME'] = $field['smart'];
            //     // $url = $hook . $method;

            //     // $response = Http::post($url, $data);

            //     // // $responseData = BitrixController::getBitrixResponse($response, 'BitrixDealDocumentService: getSmartItem');

            //     // }
            // }

            //smart fields

            // $responseData = InstallFieldsController::createFieldsForSmartProcesses($hook, $fields);
            $portalEntityFields = null;
            if ($entityType === 'deal') {
                $parentClass = BtxDeal::class;
                $parentId = $portalDeal['id'];
                $portalEntityFields =  $portalDealFields;
            } else   if ($entityType === 'company') {
                $parentClass = BtxCompany::class;
                $parentId = $portalCompany['id'];
                $portalEntityFields =  $portalCompanyFields;
            } else   if ($entityType === 'lead') {
                $parentClass = BtxLead::class;
                $parentId = $portalLead['id'];
                $portalEntityFields =  $portalLeadFields;
            } else   if ($entityType === 'smart') {
                $parentClass = Smart::class;
                if (!empty($portalSmart)) {
                    $portalEntityFields = $portalSmart->fields;


                    // Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => [
                    //     // 'portalDealFields' => $portalDealFields,
                    //     'portalEntityFields' => $portalEntityFields,
                    //     // 'portalCompanyFields' => $portalCompanyFields,
                    //     // 'portalsmarts' => $portalsmarts,
                    // ]]);
                }
            }

            if ($entityType !== 'smart') {
                $responseData = InstallFieldsController::createFieldsForEntities(
                    $entityType,
                    $hook,
                    $fields,
                    $portalEntityFields,
                    $parentClass,
                    $parentId

                );
            } else {
                // Log::channel('telegram')->info('APRIL_ONLINE TEST', [
                //     'INSTALL' => 
                //     [
                //         ' PortalSmart' => $portalSmart,
                //         ' btxSmart' => $btxSmart

                //     ]]);

                if (!empty($portalSmart)) {
                    $responseData = InstallFieldsController::createFieldsForSmartProcesses(
                        $hook,
                        $fields,
                        $group,
                        $portalSmart,
                        $parentClass,
                        $btxSmart,

                    );
                }
            }

            // };
        } catch (\Exception $e) {
            Log::error('Error in installSmart', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            Log::channel('telegram')->info('APRIL_ONLINE TEST', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return APIController::getError('An error occurred during installation', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return APIController::getSuccess(['field' => $responseData]);

        //     "crm.deal.userfield.add",
        // {
        // 	fields:
        // 	{
        // 		"FIELD_NAME": "MY_STRING",
        // 		"EDIT_FORM_LABEL": "Моя строка",
        // 		"LIST_COLUMN_LABEL": "Моя строка",
        // 		"USER_TYPE_ID": "string",
        // 		"XML_ID": "MY_STRING",
        // 		"SETTINGS": { "DEFAULT_VALUE": "Привет, мир!" }
        // 	}


        // "FIELD_NAME": "MY_LIST",
        // 	"EDIT_FORM_LABEL": "Мой список",
        // 	"LIST_COLUMN_LABEL": "Мой список",
        // 	"USER_TYPE_ID": "enumeration",
        // 	"LIST": [ { "VALUE": "Элемент #1" },
        // 		{ "VALUE": "Элемент #2" },
        // 		{ "VALUE": "Элемент #3" },
        // 		{ "VALUE": "Элемент #4" },
        // 		{ "VALUE": "Элемент #5" } ],
        // 	"XML_ID": "MY_LIST",
        // 	"SETTINGS": { "LIST_HEIGHT": 3 }

        // },
        //     Набор полей  На данный момент:
        // ENTITY_ID
        // USER_TYPE_ID
        // FIELD_NAME
        // LIST_FILTER_LABEL
        // LIST_COLUMN_LABEL
        // EDIT_FORM_LABEL
        // ERROR_MESSAGE
        // HELP_MESSAGE
        // MULTIPLE
        // MANDATORY
        // SHOW_FILTER
        // SETTINGS
        // LIST - массив вида array("поле"=>"значение"[, ...]), содержащий описание пользовательского поля.
        // В том числе содержит поле LIST, которое содержит набор значений списка для пользовательских полей типа Список. Указывается при создании/обновлении поля. Каждое значение представляет собой массив с полями:

        // VALUE - значение элемента списка. Поле является обязательным в случае, когда создается новый элемент.
        // SORT - сортировка.
        // DEF - если равно Y, то элемент списка является значением по-умолчанию. Для множественного поля допустимо несколько DEF=Y. Для не множественного, дефолтным будет считаться первое.
        // XML_ID - внешний код значения. Параметр учитывается только при обновлении уже существующих значений элемента списка.
        // ID - идентификатор значения. Если он указан, то считается что это обновление существующего значения элемента списка, а не создание нового. Имеет смысл только при вызове методов *.userfield.update.
        // DEL - если равно Y, то существующий элемент списка будет удален. Применяется, если заполнен параметр ID.
    }


    public static function createFieldsForSmartProcesses(
        $hook,
        $fields,
        $group,
        $portalsmart,
        $parentClass,
        $btxSmart,
    ) {

        $smartId = $portalsmart['bitrixId'];
        $portalsmartId = $portalsmart['id'];

        $btxSmartFields = null;

        $portalFields = $portalsmart->fields;

        // Step 1: Get all smart processes
        $url = $hook . '/userfieldconfig.list';
        $getSmartBtxFieldsData = [
            'moduleId' => 'crm',
            'filter' => [
                'entityId' => 'CRM_' . $btxSmart['id'],
                // 'entityTypeId' => $btxSmart['entityTypeId'],
            ]
        ];

        // $url = $hook . '/crm.item.fields';
        // $getSmartBtxFieldsData = [
        //     'entityTypeId' => $smartId,
        //     // 'filter' => [
        //     //     'ENTITY_ID' => 'CRM_' . $smartId
        //     // ]
        // ];

        // Log::channel('telegram')->info("hook", [
        //     'getSmartBtxFieldsData' => $getSmartBtxFieldsData,


        // ]);
        $response = Http::post($url, $getSmartBtxFieldsData);
        $resultFields = BitrixController::getBitrixResponse($response, 'Create Smart Fields - get fields');

        if (isset($resultFields['fields'])) {

            $resultFields = $resultFields['fields'];
        }



        $btxSmartFields = $resultFields;

        foreach ($fields as $field) {


            $type = $field['type'] ?? 'string';
            $multiple = 'N';

            if ($field['type'] == 'multiple') {
                $multiple =  "Y";
                $type = 'string';
            }


            if (!empty($field['smart'])) {


                $currentBtxField = false;
                $currentBtxFieldId = false;
                $currentPortalField = false;
                $enumItemsForUpdate = [];
                $currentBtxEnum = [];
                //get current btx field

                foreach ($btxSmartFields as $curBtxField) {
                    sleep(1);
                    if (
                        'UF_CRM_' . $btxSmart['id'] . '_' . $field['smart'] === $curBtxField['fieldName']

                    ) {
                        $currentBtxFieldId = $curBtxField['id'];
                        $currentBtxField = $curBtxField;

                        if ($curBtxField['userTypeId'] == 'enumeration') {

                            $currentBtxFieldId = $curBtxField['id'];
                            $currentBtxField = $curBtxField;

                            $url = $hook . '/userfieldconfig.get';
                            $getSmartBtxFieldsData = [
                                'id' => $currentBtxFieldId,
                                'moduleId' => 'crm',

                            ];
                            $response = Http::post($url, $getSmartBtxFieldsData);
                            $resultEnumField = BitrixController::getBitrixResponse($response, 'Create Smart Fields - get fields');


                            if (isset($resultEnumField['enum'])) {
                                $currentBtxEnum = $resultEnumField['enum'];
                            }
                        }
                    }
                }

                //get current portal data field
                $field['code'] = preg_replace('/[\x00-\x1F\x7F]/', '', $field['code']);
                if (!empty($portalFields)) {
                    foreach ($portalFields as $pind => $pField) {
                        if ($pField['code'] == $field['code']) {
                            $currentPortalField = $pField;
                        }
                    }
                }


                $field['type'] = preg_replace('/[\x00-\x1F\x7F]/', '', $field['type']);
                if ($field['type'] == 'enumeration') {
                    // $enumItemsForUpdate = [];
                    if (!empty($field['list'])) {

                        foreach ($field['list'] as $gItem) {
                            $currentItem = null;
                            $currentItemBtxId = null;
                            if (!empty($currentBtxEnum)) {

                                foreach ($currentBtxEnum as $btxEnumItem) {
                                    $gItem['XML_ID'] = preg_replace('/[\x00-\x1F\x7F]/', '', $gItem['XML_ID']);
                                    if ($btxEnumItem['xmlId'] === $gItem['XML_ID'])
                                        // Log::channel('telegram')->error("setFieldItem add", [
                                        //     'currentPortalField' => $currentPortalField,


                                        // ]);
                                        $currentItem = $btxEnumItem;
                                }
                            }

                            if (!$currentItem && !empty($currentItemBtxId)) {
                                $currentItem = [];
                            }
                            $currentItem['value'] = preg_replace('/[\x00-\x1F\x7F]/', '',  $gItem['VALUE']);
                            $currentItem['sort'] = preg_replace('/[\x00-\x1F\x7F]/', '',  $gItem['SORT']);
                            $currentItem['xmlId'] = preg_replace('/[\x00-\x1F\x7F]/', '',  $gItem['XML_ID']);

                            array_push($enumItemsForUpdate, $currentItem);
                        }
                    }
                }







                $fieldBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['smart']);
                $fieldNameUpperCase = 'UF_CRM_' . $btxSmart['id'] . '_' . $fieldBitrixId;
                $fieldNameCamelCase = 'ufCrm' . $btxSmart['id'] . '_' . $fieldBitrixId;
                $field['name'] = preg_replace('/[\x00-\x1F\x7F]/', '', $field['name']);
                $fieldsData = [
                    "moduleId" => "crm",
                    "field" => [
                        'entityId' => 'CRM_' . $btxSmart['id'],
                        // "entityId" => 'CRM_' . $smartId,
                        "fieldName" => $fieldNameUpperCase,
                        "userTypeId" => $type,
                        "multiple" => $multiple,
                        "xmlId" => $fieldBitrixId,
                        // "mandatory" => $mandatory,
                        "editFormLabel" => ["ru" => $field['name']],
                        "enum" => $enumItemsForUpdate
                        // "enum" => $type === 'enumeration' ? array_map(function ($item, $key) {
                        //     return [
                        //         "value" => $item,
                        //         "sort" => $key + 1,
                        //         "def" => 'N',
                        //     ];
                        // }, $field['list'], array_keys($field['list'])) : [],
                    ]
                ];

                $method = '/userfieldconfig.add';

                if ($currentBtxField) {
                    $fieldsData['id'] = $currentBtxFieldId;
                    $method = '/userfieldconfig.update';
                }
                $url = $hook . $method;
                $response = Http::post($url, $fieldsData);
                // sleep(1);
                $updtdBtxField = BitrixController::getBitrixResponse($response, 'smart: fields' . $fieldsData['field']['fieldName']);

                if (isset($updtdBtxField['field'])) {
                    $updtdBtxField = $updtdBtxField['field'];
                }

                if (!$currentPortalField) {
                    $currentPortalField = new Bitrixfield();
                    $currentPortalField->entity_type = $parentClass;
                    $currentPortalField->entity_id = $portalsmartId;
                    $appTypeBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['appType']);
                    $currentPortalField->parent_type = $appTypeBitrixId;
                }
                $typeBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['type']);
                $nameBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['name']);
                $codeBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['code']);
                $currentPortalField->type = $typeBitrixId;
                $currentPortalField->title = $nameBitrixId;
                $currentPortalField->name = $nameBitrixId;
                $currentPortalField->code = $codeBitrixId;


                $currentPortalField->bitrixId = $fieldNameUpperCase;
                $currentPortalField->bitrixCamelId = $fieldNameCamelCase;


                $currentPortalField->save();



                if ($field['type'] == 'enumeration') {
                    $portalFieldItems = $currentPortalField->items;

                    Log::channel('telegram')->error("updtdBtxField", [
                        'portalFieldItems' => $portalFieldItems,


                    ]);


                    if (!empty($updtdBtxField)) {
                        if (!empty($updtdBtxField['enum'])) {
                            $currentBtxEnum = $updtdBtxField['enum'];

                            foreach ($currentBtxEnum as $currentFieldItem) {

                                $currentPortalItem = null;


                                if (!empty($portalFieldItems)) {
                                    foreach ($portalFieldItems as $pitem) {

                                        if ($currentFieldItem['xmlId'] == $pitem['code']) {

                                            if (!empty($pitem['id'])) {
                                                $currentPortalItem  = BitrixfieldItem::find($pitem['id']);
                                            }
                                        }
                                    }
                                }

                                if (!$currentPortalItem) {       // если на портале не существуют item - создаем  


                                    $currentPortalItem  =  new BitrixfieldItem();
                                    $currentPortalItem->bitrixfield_id = $currentPortalField['id'];
                                } else { // если на портале существуют item - обновляем

                                }
                                $currentPortalItem->bitrixId = $currentFieldItem['id'];

                                $currentPortalItem->name = $currentFieldItem['value'];
                                $currentPortalItem->title = $currentFieldItem['value'];
                                $currentPortalItem->code = $currentFieldItem['xmlId'];
                                $currentPortalItem->save();
                            }
                        }
                    }
                }



                // Log::channel('telegram')->error("setFieldItem add", [
                //     'userfieldconfig.add' => $responseData,


                // ]);

                // Log::channel('telegram')->error("setFieldItem add", [
                //     'currentPortalField' => $currentPortalField,


                // ]);
            }
        }
    }

    public static function createFieldsForEntities(
        $entityType,
        $hook,
        $fields,
        $portalFields,
        $parentClass,
        $parentId,
    ) {
        // $entityType lead company deal
        // Step 1: Get all smart processes
        $url = $hook . '/crm.' . $entityType . '.userfield.list';
        $response = Http::post($url);
        $currentFields = BitrixController::getBitrixResponse($response, 'install :createFieldsForEntities');

        if (isset($currentFields['items'])) {
            $currentFields = $currentFields['items'];
        }
        if (isset($currentFields['fields'])) {
            $currentFields = $currentFields['fields'];
        }


        $responsesData = [];
        foreach ($fields as  $index => $field) {
            $currentBtxField = false;
            $currentBtxFieldId = false;
            $currentPortalField = false;

            if ($field[$entityType]) {


                foreach ($currentFields as $curBtxField) {
                    if ($field['code'] === $curBtxField['XML_ID']) {
                        $currentBtxFieldId = $curBtxField['ID'];
                        $currentBtxField = $curBtxField;

                        // if($field['type'] == 'enumeration'){
                        //     Log::channel('telegram')->error("responseData", [
                        //         '$currentBtxField' => $currentBtxField['XML_ID'],
                        //         '$field' => $field['code'],
                        //         'field' => $field,

                        //     ]);
                        // }
                    }
                }

                if (!empty($portalFields)) {

                    foreach ($portalFields as $portalField) {
                        if ($field['code'] === $portalField['code']) {
                            $currentPortalField = $portalField;
                        }
                    }
                }

                // if($index < 10){
                //     Log::channel('telegram')->error("currentPortalField", [
                //         'currentPortalField' => $currentPortalField,
                //         // 'currentPortalField' => $currentPortalField,

                //     ]);
                // // }



                // if (!empty($field[$entityType])) {
                $type = $field['type'] ?? 'string';
                $multiple = 'N';

                if ($field['type'] == 'multiple') {
                    $multiple =  "Y";
                    $type = 'string';
                }
                // $mandatory = $field['mandatory'] ?? 'N';
                // $fieldNameUpperCase = 'UF_CRM_' . $smartId . '_' . strtoupper($field['smart']);

                $fieldsData = [ //list

                    "EDIT_FORM_LABEL" => $field['name'],
                    "LIST_COLUMN_LABEL" => $field['name'],
                    "USER_TYPE_ID" => $type,
                    'MULTIPLE' => $multiple,
                    // "LIST" => $field['list'],
                    // "CODE" => $field['code'],
                    "XML_ID" => $field['code'],
                    "SETTINGS" => ["LIST_HEIGHT" => 1],
                    "SORT" => $field['order'],
                ];

                $data = [
                    'fields' => $fieldsData
                ];
                $method = '/crm.' . $entityType . '.userfield.add';

                if ($currentBtxFieldId) {


                    $data['id'] = $currentBtxFieldId;
                    $method = '/crm.' . $entityType . '.userfield.update';
                } else {
                    $data['fields']["LIST"] = $field['list'];
                    $data['fields']["FIELD_NAME"] = $field[$entityType];
                }
                $url = $hook . $method;
                $response = Http::post($url, $data);

                $responseData = BitrixController::getBitrixResponse($response, 'fields install');
                array_push($responsesData, $responseData);
                if (!$currentBtxFieldId && $responseData) {
                    $currentBtxFieldId = $responseData;
                }
                sleep(1);

                // } else {
                //TODO найти такой на сервере БД и удалить
                // }

                // Log::channel('telegram')->error("fieldsData", [
                //     'responseData' => $responseData,

                // ]);

                if (!$currentPortalField) {
                    $currentPortalField = new Bitrixfield();
                    $currentPortalField->entity_type = $parentClass;
                    $currentPortalField->entity_id = $parentId;
                    $appTypeBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['appType']);
                    $currentPortalField->parent_type = $field['appType'];
                }
                $typeBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['type']);
                $nameBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['name']);
                $codeBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field['code']);
                $currentPortalField->type = $typeBitrixId;
                $currentPortalField->title = $nameBitrixId;
                $currentPortalField->name = $nameBitrixId;
                $currentPortalField->code = $codeBitrixId;

                $fieldBitrixId = preg_replace('/[\x00-\x1F\x7F]/', '',  $field[$entityType]);
                $currentPortalField->bitrixId = $fieldBitrixId;
                $currentPortalField->bitrixCamelId = 'ufCrm' . $fieldBitrixId;


                $currentPortalField->save();
                $field['type'] = preg_replace('/[\x00-\x1F\x7F]/', '', $field['type']);
                if ($field['type'] == 'enumeration') {
                    $updtedField = $currentBtxField;

                    if ($currentBtxFieldId) {
                        $method = '/crm.' . $entityType . '.userfield.get';
                        $url = $hook . $method;
                        $response = Http::post($url, [
                            'id' => $currentBtxFieldId
                        ]);
                        $updtedField = BitrixController::getBitrixResponse($response, 'fields install');

                        $resultList = [];

                        if (!empty($updtedField['LIST'])) {
                            foreach ($updtedField['LIST'] as $currentBtxItem) {
                                $searchingItem = null;
                                foreach ($field['list'] as $gooItem) {
                                    // определяем элементы которые надо отредактировать
                                    // if(isset($currentBtxItem['XML_ID'])){
                                    $gooItem['VALUE'] = preg_replace('/[\x00-\x1F\x7F]/', '', $gooItem['VALUE']);
                                    if ($gooItem['VALUE'] === $currentBtxItem['VALUE']) {
                                        // $gooItem['ID'] == $currentBtxItem['ID'];
                                        $searchingItem = [
                                            ...$gooItem,
                                            'ID' => $currentBtxItem['ID']
                                        ];
                                    }

                                    // }

                                }
                                if (!$searchingItem) {

                                    $currentBtxItem['DEL'] = 'Y';
                                    $searchingItem = $currentBtxItem;
                                }
                                array_push($resultList, $gooItem);
                            }
                            dd($field['list']);
                            foreach ($field['list'] as $gooItem) {
                                foreach ($resultList as $resItem) {
                                    if ($resItem['XML_ID'] !== $gooItem['XML_ID']) {
                                        array_push($resultList, $gooItem);
                                    }
                                }
                            }
                        } else {
                            $resultList = $field['list'];
                        }
                        $data = [
                            'id' => $currentBtxFieldId,
                            'fields' => [
                                'LIST' => $resultList
                            ]
                        ];
                        // Log::channel('telegram')->error("setFieldItems currentBtxField", [
                        //     'data' => $data,


                        // ]);
                        // sleep(1);
                        $method = '/crm.' . $entityType . '.userfield.update';
                        $response = Http::post($url, $data);
                        // $updtedField = BitrixController::getBitrixResponse($response, 'fields install');
                        sleep(1);

                        $method = '/crm.' . $entityType . '.userfield.get';
                        $updtgetresponse = Http::post($url, ['id' => $currentBtxFieldId]);
                        $updtedField = BitrixController::getBitrixResponse($updtgetresponse, 'fields install updtgetresponse');
                    }

                    $items = InstallFieldsController::setFieldItems($updtedField, $field, $currentPortalField);
                }
            }

            // sleep(2);
        }


        // }
    }

    public static function setFieldItems(
        $currentBtxField,    //from btx
        $field,  //from google
        $currentPortalField  //from db
    ) {
        // $url = $hook . '/crm.' . $entityType . '.userfield.get';
        // $data = [
        //     'id' => $currentBtxFieldId
        // ];
        // $response = Http::post($url, $data);
        // $currentField = BitrixController::getBitrixResponse($response, 'install :createFieldsForEntities');
        // Log::channel('telegram')->error("setFieldItems currentBtxField", [
        //     'currentBtxField' => $currentBtxField,


        // ]);
        $currentField = $currentBtxField;
        $currentFieldItems = null;
        // Log::channel('telegram')->error("setFieldItem", [
        //     'currentBtxField' => $currentBtxField,
        //     'field' => $field,
        //     'currentPortalField' => $currentPortalField,

        // ]);

        if (isset($currentField['LIST'])) {
            $currentFieldItems = $currentField['LIST'];
        }

        if (!empty($currentPortalField)) {

            if (isset($currentPortalField->items)) {
                $portalFieldItems = $currentPortalField->items->toArray();
            }
        }

        $currentPortalItem  = false;
        $currentGooItem  = false;
        if (!empty($currentFieldItems)) {
            foreach ($currentFieldItems as $currentFieldItem) {  //btx items
                // Log::channel('telegram')->error("setFieldItem", [
                //     'currentFieldItem' => $currentFieldItem,


                // ]);
                if (!empty($portalFieldItems)) {
                    foreach ($portalFieldItems as $pitem) {
                        sleep(1);
                        if ($currentFieldItem['VALUE'] == $pitem['title']) {

                            if (!empty($pitem['id'])) {
                                $currentPortalItem  = BitrixfieldItem::find($pitem['id']);
                            } else {
                                $currentPortalItem  = $pitem;
                            }
                        }
                    }
                }

                // Log::channel('telegram')->error("setFieldItem field", [
                //     'field' => $field,


                // ]);
                if (!empty($field['list'])) {
                    foreach ($field['list'] as $gitem) {


                        $gitem['VALUE'] = preg_replace('/[\x00-\x1F\x7F]/', '', $gitem['VALUE']);
                        if ($currentFieldItem['VALUE'] == $gitem['VALUE']) {

                            $currentGooItem  =  $gitem;
                            // if ($field['name'] == 'Тип договора') {
                            //     // Log::channel('telegram')->error("setFieldItem Тип договора", [
                            //     //     'currentFieldItem' => $currentFieldItem,
                            //     //     'gitem' => $gitem,
                            //     //     'currentPortalItem' => $currentPortalItem,

                            //     // ]);
                            // }

                            if (!$currentPortalItem) {       // если на портале не существуют item - создаем  

                                // if ($field['name'] == 'Тип договора') {
                                //     Log::channel('telegram')->error("setFieldItem new BitrixfieldItem", [
                                //         'currentGooItem' => $currentFieldItem,


                                //     ]);
                                // }

                                $currentPortalItem  =  new BitrixfieldItem();
                                $currentPortalItem->bitrixfield_id = $currentPortalField['id'];

                                if ($currentGooItem) {
                                    $currentGooItem['XML_ID'] = preg_replace('/[\x00-\x1F\x7F]/', '', $currentGooItem['XML_ID']);
                                    $currentPortalItem->code = $currentGooItem['XML_ID'];
                                }
                            } else { // если на портале существуют item - обновляем

                            }
                            $currentPortalItem->bitrixId = $currentFieldItem['ID'];

                            $currentPortalItem->name = $currentFieldItem['VALUE'];
                            $currentPortalItem->title = $currentFieldItem['VALUE'];
                            $currentPortalItem->save();
                        } else {
                            // if ($field['name'] == 'Тип договора') {
                            //     Log::channel('telegram')->error("!===== Тип договора", [
                            //         'currentFieldItem' => $currentFieldItem,
                            //         'gitem' => $gitem,
                            //         'currentPortalItem VALUE' => $currentFieldItem['VALUE'],
                            //         'gitem VALUE' => $gitem['VALUE'],

                            //     ]);
                            // }
                        }
                    }
                }


                $currentPortalItem  = false;
            }





            // if (!empty($portalFieldItems)) {

            //     foreach ($portalFieldItems as $pitem) {
            //         $pItemForDelete = false;
            //         foreach ($field['list'] as $currentFieldItem) {  //btx items

            //             if (
            //                 $pitem['title'] === $currentFieldItem['VALUE'] &&
            //                 $pitem['code'] !== $currentFieldItem['XML_ID']
            //             ) {
            //                 $pitem =  BitrixfieldItem::find($pitem['id']);
            //             }
            //         }
            //         if ($pItemForDelete) {


            //             $pitem->delete();
            //         }
            //     }
            // }
        }
    }
}
