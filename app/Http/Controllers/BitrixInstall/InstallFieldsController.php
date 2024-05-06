<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\PortalController;
use FontLib\Table\Type\name;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InstallFieldsController extends Controller
{

    static function setFields(
        $token
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
        // $domain = 'gsr.bitrix24.ru';

        $hook = BitrixController::getHook($domain);

        // $fields = [ //string
        //     "FIELD_NAME" => "MY_STRING",
        //     "EDIT_FORM_LABEL" => "Моя строка",
        //     "LIST_COLUMN_LABEL" => "Моя строка",
        //     "USER_TYPE_ID" => "string",
        //     "XML_ID" => "MY_STRING",
        //     "SETTINGS" => ["DEFAULT_VALUE" => "Привет, мир!"]
        // ];
        $portal = PortalController::innerGetPortal($domain);
        Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => ['portal' => $portal]]);

        $categories = null;
        $url = 'https://script.google.com/macros/s/' . $token . '/exec';
        $response = Http::get($url);

        if ($response->successful()) {
            $googleData = $response->json();
            Log::channel('telegram')->error("googleData", [
                'googleData' => $googleData,

            ]);
        } else {
            Log::channel('telegram')->error("Failed to retrieve data from Google Sheets", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return response(['resultCode' => 1, 'message' => 'Error retrieving data'], 500);
        }




        $webhookRestKey = $portal['portal']['C_REST_WEB_HOOK_URL'];
        $hook = 'https://' . $domain . '/' . $webhookRestKey;
        Log::channel('telegram')->info('APRIL_ONLINE TEST', ['INSTALL' => ['hook' => $hook]]);
        // $methodSmartInstall = '/crm.type.add.json';
        // $url = $hook . $methodSmartInstall;

        // Проверка на массив
        if (!empty($googleData['fields'])) {
            $fields = $googleData['fields'];
         
            foreach ($fields as $field) {
                $method = '/crm.deal.userfield.add';
                $url = $hook . $method;
                $fieldsData = [ //list
                    "FIELD_NAME" => $field['deal'],
                    "EDIT_FORM_LABEL" => $field['name'],
                    "LIST_COLUMN_LABEL" => $field['name'],
                    "USER_TYPE_ID" => $field['type'],
                    "LIST" => $field['list'],
                    "XML_ID" => $field['code'],
                    "SETTINGS" => ["LIST_HEIGHT" => 1],
                    // "ORDER" => 2
                ];

                $data = [
                    'fields' => $fieldsData
                ];
                $response = Http::post($url, $data);
                $responseData = BitrixController::getBitrixResponse($response, 'BitrixDealDocumentService: getSmartItem');

                $method = '/crm.company.userfield.add';
                $fieldsData['FIELD_NAME'] = $field['company'];
                $url = $hook . $method;

                $response = Http::post($url, $data);
                $responseData = BitrixController::getBitrixResponse($response, 'BitrixDealDocumentService: getSmartItem');

                $method = '/crm.lead.userfield.add';
                $fieldsData['FIELD_NAME'] = $field['lead'];
                $url = $hook . $method;

                $response = Http::post($url, $data);
                $responseData = BitrixController::getBitrixResponse($response, 'BitrixDealDocumentService: getSmartItem');

            }
        };


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
}