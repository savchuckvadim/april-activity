<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use App\Models\Bitrixfield;
use App\Models\BitrixfieldItem;
use App\Models\BtxDeal;
use App\Models\Portal;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstallFieldsController extends Controller
{

    static function setFields(
        $token,

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
        $domain = 'april-dev.bitrix24.ru';
        $responseData = null;
        // $domain = 'gsr.bitrix24.ru';
        try {
            $hook = BitrixController::getHook($domain);

            // $fields = [ //string
            //     "FIELD_NAME" => "MY_STRING",
            //     "EDIT_FORM_LABEL" => "Моя строка",
            //     "LIST_COLUMN_LABEL" => "Моя строка",
            //     "USER_TYPE_ID" => "string",
            //     "XML_ID" => "MY_STRING",
            //     "SETTINGS" => ["DEFAULT_VALUE" => "Привет, мир!"]
            // ];
            $portal = Portal::where('domain', $domain)->first();
            $webhookRestKey = $portal->getHook();
            $portalDeal = $portal->deal();
            $portalLead = $portal->lead();
            $portalCompany = $portal->company();
            $portalsmarts = $portal->smarts;


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
            // if(!empty($portalsmarts)){
            //     $portalportalsmartsFields = $portalCompany->fields;
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

            // Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => [
            //     // 'portalDealFields' => $portalDealFields,
            //     'fields' => $fields,
            //     // 'portalCompanyFields' => $portalCompanyFields,
            //     // 'portalsmarts' => $portalsmarts,
            // ]]);
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
            $parentClass = BtxDeal::class;
            $parentId = $portalDeal['id'];
            $responseData = InstallFieldsController::createFieldsForEntities(
                'deal',
                $hook,
                $fields,
                $portalDealFields,
                $parentClass,
                $parentId

            );
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


    public static function createFieldsForSmartProcesses($hook, $fields)
    {
        // Step 1: Get all smart processes
        $url = $hook . '/crm.type.list';
        $response = Http::post($url);
        $smartProcesses = $response->json()['result']['types'];

        // Step 2: Filter smart processes
        $keywords = ['Продажи', 'Гарант', 'ТМЦ'];
        $filteredSmartProcesses = array_filter($smartProcesses, function ($process) use ($keywords) {
            foreach ($keywords as $keyword) {
                if (stripos($process['title'], $keyword) !== false) {
                    return true;
                }
            }
            return false;
        });

        // Step 3: Create user fields for filtered smart processes
        foreach ($filteredSmartProcesses as $smartProcess) {
            $smartId = $smartProcess['id'];
            foreach ($fields as $field) {

                if (!empty($field['smart'])) {

                    $multiple = 'N';
                    $type = $field['type'] ?? 'string';
                    if ($type == 'multiple') {
                        $multiple =  "Y";
                        $type = 'string';
                    }

                    // $mandatory = $field['mandatory'] ?? 'N';
                    $fieldNameUpperCase = 'UF_CRM_' . $smartId . '_' . strtoupper($field['smart']);

                    $fieldsData = [
                        "moduleId" => "crm",
                        "field" => [
                            "entityId" => 'CRM_' . $smartId,
                            "fieldName" => $fieldNameUpperCase,
                            "userTypeId" => $type,
                            "multiple" => $multiple,
                            // "mandatory" => $mandatory,
                            "editFormLabel" => ["ru" => $field['name']],
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
                    $url = $hook . $method;
                    $response = Http::post($url, $fieldsData);
                    sleep(2);
                    $responseData = BitrixController::getBitrixResponse($response, 'smart: fields');
                }
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
        // Log::channel('telegram')->error("fieldsData", [
        //     'currentFields' => $currentFields[0],

        // ]);

        // Log::channel('telegram')->error("fieldsData", [
        //     'portalFields' => $portalFields,

        // ]);

        $responsesData = [];
        foreach ($fields as  $index => $field) {
            $currentBtxField = false;
            $currentBtxFieldId = false;
            $currentPortalField = false;


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

            foreach ($portalFields as $portalField) {
                if ($field['code'] === $portalField['code']) {
                    $currentPortalField = $portalField;
                }
            }
            // if($index < 10){
            //     Log::channel('telegram')->error("currentPortalField", [
            //         'currentPortalField' => $currentPortalField,
            //         'currentPortalField' => $currentPortalField,

            //     ]);
            // }



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
                "SORT" => 134,
            ];

            $data = [
                'fields' => $fieldsData
            ];
            $method = '/crm.' . $entityType . '.userfield.add';

            if ($currentBtxFieldId) {


                $data['id'] = $currentBtxFieldId;
                $method = '/crm.' . $entityType . '.userfield.update';
            } else {

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

                $currentPortalField->parent_type = $field['appType'];
            }
            $currentPortalField->type = $field['type'];
            $currentPortalField->title = $field['name'];
            $currentPortalField->name = $field['name'];
            $currentPortalField->code = $field['code'];
            $currentPortalField->bitrixId = $field[$entityType];
            $currentPortalField->bitrixCamelId = 'ufCrm' . $field[$entityType];
            $currentPortalField->save();
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

                        foreach ($field['list'] as $gooItem) {
                            foreach ($resultList as $resItem) {
                                if ($resItem['XML_ID'] !== $gooItem['XML_ID']) {
                                    array_push($resultList, $gooItem);
                                }
                            }
                        }


                       
                    }
                    $data = [
                        'id' => $currentBtxFieldId,
                        'fields' => [
                            'LIST' => $resultList
                        ]
                    ];
                    sleep(1);
                    $method = '/crm.' . $entityType . '.userfield.update';
                    $response = Http::post($url, $data);
                    $updtedField = BitrixController::getBitrixResponse($response, 'fields install');
                   
                }

                $items = InstallFieldsController::setFieldItems($updtedField, $field, $currentPortalField);
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
        Log::channel('telegram')->error("setFieldItems currentBtxField", [
            'currentBtxField' => $currentBtxField,


        ]);
        $currentField = $currentBtxField;
        $currentFieldItems = null;

        if (isset($currentField['LIST'])) {
            $currentFieldItems = $currentField['LIST'];
        }

        if (!empty($currentPortalField)) {

            if (isset($currentPortalField->items)) {
                $portalFieldItems = $currentPortalField->items->toArray();
            }
        }

        $currentPortalItem  = false;
        if (!empty($currentFieldItems)) {
            foreach ($currentFieldItems as $currentFieldItem) {  //btx items
                
                if (!empty($portalFieldItems)) {
                    foreach ($portalFieldItems as $pitem) {

                        if ($currentFieldItem['VALUE'] == $pitem['title']) {

                            $currentPortalItem  =  $pitem;
                        }
                    }
                }

                if (!$currentPortalItem) {
                    $currentPortalItem  =  new BitrixfieldItem();
                    $currentPortalItem->bitrixfield_id = $currentPortalField['id'];
                }
                $currentPortalItem->bitrixId = (int)$currentFieldItem['ID'];
                $currentPortalItem->code = $field['XML_ID'];
                $currentPortalItem->name = $currentFieldItem['VALUE'];
                $currentPortalItem->title = $currentFieldItem['VALUE'];
                $currentPortalItem->save();
                $currentPortalItem  = false;
            }
           




            if (!empty($portalFieldItems)) {

                foreach ($portalFieldItems as $pitem) {
                    $pItemForDelete = false;
                    foreach ($currentFieldItems as $currentFieldItem) {  //btx items

                        if (
                            $pitem['title'] === $currentFieldItem['VALUE'] &&
                            $pitem['bitrixId'] !== $currentFieldItem['ID']
                        ) {
                            $pItemForDelete = $pitem;
                        }
                    }
                    if ($pItemForDelete) {
                        Log::channel('telegram')->error("currentPortalField", [
                            'pItemForDelete' => $pItemForDelete,
                            'currentFieldItem VALUE' => $currentFieldItem['VALUE'],

                        ]);
                        $pitem->delete();
                    }
                }
            }
        }
    }
}
