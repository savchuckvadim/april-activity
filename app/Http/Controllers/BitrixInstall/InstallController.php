<?php

namespace App\Http\Controllers\BitrixInstall;

use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class InstallController extends Controller
{
    static function field()
    {
        // $domain = 'april-dev.bitrix24.ru';
        $domain = 'gsr.bitrix24.ru';
        $method = '/crm.deal.userfield.add';
        $hook = BitrixController::getHook($domain);
        $url = $hook . $method;
        // $fields = [ //string
        //     "FIELD_NAME" => "MY_STRING",
        //     "EDIT_FORM_LABEL" => "Моя строка",
        //     "LIST_COLUMN_LABEL" => "Моя строка",
        //     "USER_TYPE_ID" => "string",
        //     "XML_ID" => "MY_STRING",
        //     "SETTINGS" => ["DEFAULT_VALUE" => "Привет, мир!"]
        // ];
        $fields = [ //list
            "FIELD_NAME" => "1684144993",
            "EDIT_FORM_LABEL" => "Тип Договора",
            "LIST_COLUMN_LABEL" => "Тип Договора",
            "USER_TYPE_ID" => "enumeration",
            "LIST" => [
                ["VALUE" => "Интернет"],
                ["VALUE" => "Проксима"],
                ["VALUE" => "Абонемент"],
                ["VALUE" => "Лицензия"],
                ["VALUE" => "Передача ключа"]

            ],
            "XML_ID" => "CONTRACT_TYPE",
            "SETTINGS" => ["LIST_HEIGHT" => 1],
            "ORDER" => 2
        ];

        $data = [
            'fields' => $fields
        ];
        $response = Http::get($url, $data);
        // $responseData = $response->json();
        $responseData = BitrixController::getBitrixResponse($response, 'BitrixDealDocumentService: getSmartItem');
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
