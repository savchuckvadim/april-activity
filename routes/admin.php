<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\CounterController;

use App\Http\Controllers\FieldController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FItemController;

use App\Http\Controllers\InfoblockController;
use App\Http\Controllers\InfoGroupController;

use App\Http\Controllers\PortalController;

use App\Http\Controllers\RqController;
use App\Http\Controllers\TemplateController;

use App\Models\Infoblock;
use App\Models\InfoGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['api.key', 'ajax.only'])->group(function () {




    // Route::post('getportal', function (Request $request) {
    //     $domain  = $request->input('domain');
    //     return PortalController::getPortal($domain);
    // });




    //initial
    //from rq - передается rq Id
    Route::get('initial/rq/{rqId}/counter', function ($rqId) {

        return CounterController::getInitial($rqId);
    });

    //rq id не передается в initial формируется список всех rq для выбора
    Route::get('initial/counter', function () {

        return CounterController::getInitial();
    });



    //get
    //collection
    Route::get('rq/{rqId}/counters', function () {

        return CounterController::getAll();
    });

    //item
    Route::get('counter/{counterId}/', function ($counterId) {

        return CounterController::get($counterId);
    });

    
    //set
    Route::post('counter', function ($rqId) {

        return CounterController::set($rqId);
    });









    Route::get('template/{templateId}/fields', function ($templateId) {
        return FieldController::getFields($templateId);
    });
    Route::get('template/{templateId}/providers', function ($templateId) {
        return TemplateController::getProviders($templateId);
    });
    Route::get('template/{templateId}/counters', function ($templateId) {
        return TemplateController::getCounters($templateId);
    });


    Route::get('field/{fieldId}/items', function ($fieldId) {
        return FItemController::getFitems($fieldId);
    });

    Route::get('portal/{portalId}/providers', function ($portalId) {
        return PortalController::getProviders($portalId);
    });
    Route::get('portal/{portalId}/templates', function ($portalId) {
        return PortalController::getTemplates($portalId);
    });
    Route::get('portal/{portalId}/smarts', function ($portalId) {
        return PortalController::getSmarts($portalId);
    });
    Route::get('portal/{portalId}/bitrixlists', function ($portalId) {
        return PortalController::getBitrixlists($portalId);
    });
    Route::get('portal/{portalId}/departaments', function ($portalId) {
        return PortalController::getDepartaments($portalId);
    });
    Route::get('portal/{portalId}/timezones', function ($portalId) {
        return PortalController::getTimezones($portalId);
    });
    Route::get('portal/{portalId}/callingGroups', function ($portalId) {
        return PortalController::getCallingGroups($portalId);
    });




    Route::get('rq/{rqId}/{fileType}', function ($rqId, $fileType) {
        return RqController::getFiles($rqId, $fileType);
    });

    //////////////////////////////CLIENTS
    //////PORTAL





    Route::get('portal/{portalId}', function ($portalId) {
        return PortalController::getPortalById($portalId);
    });

    Route::delete('portal/{portalId}', function ($portalId) {
        return PortalController::deletePortal($portalId);
    });
    Route::get('portals', function () {
        return PortalController::getPortals();
    });
    Route::post('portal', function (Request $request) {
        $number  = $request->input('number');
        $domain  = $request->input('domain');
        $key = $request->input('key'); //placement key
        $clientId  = $request->input('clientId'); //from bitrix server api app
        $secret = $request->input('clientSecret'); //from bitrix server api app
        $hook = $request->input('hook'); //placement url
        return PortalController::setPortal($number, $domain, $key, $clientId, $secret, $hook);
    });

    Route::get('initial/portal', function () {

        return PortalController::getInitial();
    });

    Route::post('providers', function (Request $request) {
        $providers  = $request->input('providers');

        return AgentController::setProviders($providers);
    });
    Route::post('rqs', function (Request $request) {
        $rqs  = $request->input('rqs');

        return RqController::setRqs($rqs);
    });


    Route::get('providers', function () {

        return AgentController::getProviders();
    });

    Route::get('rqs', function () {

        return RqController::getRqs();
    });

    Route::get('provider/{providerId}', function ($providerId) {

        return AgentController::getProvider($providerId);
    });

    Route::get('rq/{rqId}', function ($rqId) {

        return RqController::getRq($rqId);
    });

    Route::delete('rq/{rqId}', function ($rqId) {
        return RqController::deleteRq($rqId);
    });
    Route::delete('provider/{providerId}', function ($providerId) {
        return AgentController::deleteProvider($providerId);
    });




    //////////////////TEMPLATES FIELDS FITEMS

    Route::post('fields', function (Request $request) {
        $tfields  = $request->input('fields');
        $fields  =  $tfields['fields'];
        $items  = $tfields['items'];

        return FieldController::setFields($fields, $items);
    });





    Route::post('templates', function (Request $request) {
        $templates  = $request->input('templates');
        return TemplateController::setTemplates($templates);
    });



    //GET COLLECTIONS
    //// specific



    //// no specific


    // Route::get('templates', function () {
    //     return TemplateController::getAllTemplates();
    // });

    Route::get('templates/{domain}', function ($domain) {
        return TemplateController::getTemplates($domain);
    });



    Route::get('fields/{templateId}', function ($templateId) {
        return FieldController::getFields($templateId);
    });
    // Route::get('fields', function () {
    //     return FieldController::getAllFields();
    // });
    Route::get('items/{fieldId}', function ($fieldId) {
        return FItemController::getFitems($fieldId);
    });


    // Route::get('{entityType}', function ($entityType) {
    //     return BaseController::getCollection($entityType, null, null);
    // });



    //GET ITEM
    //// no specific
    // Route::get('template/{templateId}', function ($templateId) {
    //     return TemplateController::getTemplate($templateId);
    // });

    // Route::get('field/{fieldId}', function ($fieldId) {
    //     return FieldController::getField($fieldId);
    // });
    // Route::get('item/{fitemId}', function ($fitemId) {
    //     return FItemController::getFitem($fitemId);
    // });


    //INITIAL SET
    //// specific
    // Route::get('initial/template/{templateId}/field', function ($templateId) {
    //     return FieldController::getInitialField();
    // });
    // Route::get('initial/field/{fieldId}/item', function () {
    //     return FItemController::getInitialFitem();
    // });

    //// no specific

    // Route::get('initial/template', function (Request $request) {

    //     return TemplateController::initialTemplate();
    // });

    // Route::get('initial/field', function () {
    //     return FieldController::getInitialField();
    // });
    // Route::get('initial/item', function () {
    //     return FItemController::getInitialFitem();
    // });

    // Route::get('initial/logo', function () {
    //     return FileController::getInitial();
    // });
    // Route::get('initial/stamp', function () {
    //     return FileController::getInitial();
    // });
    // Route::get('initial/signature', function () {
    //     return FileController::getInitial();
    // });
    // Route::get('initial/file', function () {
    //     return FileController::getInitial();
    // });




    //SET 
    //// specific
    Route::post('template/{templateId}/field', function ($templateId, Request $request) {
        $fieldData = [
            'name' => $request['name'],
            'type' => $request['type'],
            'code' => $request['code'],
            'isGeneral' => $request['isGeneral'],
            'isDefault' => $request['isDefault'],
            'isRequired' => $request['isRequired'],
            'value' => $request['value'],
            'description' => $request['description'],
            'bitixId' => $request['bitixId'],
            'bitrixTemplateId' => $request['bitrixTemplateId'],
            'isActive' => $request['isActive'],
            'isPlural' => $request['isPlural'],
            'isClient' => $request['isClient'],
            'img' => $request['img'],

        ];
        if ($request->hasFile('img_0')) {
            $file = $request->file('img_0');

            // Проверяем, является ли файл экземпляром UploadedFile и был ли он успешно загружен
            if ($file instanceof Illuminate\Http\UploadedFile && $file->isValid()) {
                // Обрабатываем файл, например, сохраняем его
                // $filePath = $file->store('path/to/store', 'disk_name');

                // Сохраняем путь к файлу в $fieldData
                $fieldData['img'] = $file;
            }
        }
        // return APIController::getSuccess(['fieldData' => $fieldData]);
        return FieldController::setField($templateId, $fieldData);
    });
    Route::post('field/{fieldId}/item', function ($fieldId, Request $request) {
        $fieldData = [
            'number' => $request['number'],
            'code' => $request['code'],
            'fieldNumber' => $request['fieldNumber'],
            'order' => $request['order'],
            'value' => $request['value'],
            'bitrixId' => $request['bitrixId'],

        ];

        return FItemController::setFitem($fieldId, $fieldData);
    });
    Route::post('rq/{fieldId}/item', function ($fieldId, Request $request) {
        $fieldData = [
            'number' => $request['number'],
            'code' => $request['code'],
            'fieldNumber' => $request['fieldNumber'],
            'order' => $request['order'],
            'value' => $request['value'],
            'bitrixId' => $request['bitrixId'],

        ];

        return FItemController::setFitem($fieldId, $fieldData);
    });







    //// no specific

    Route::post('template', function (Request $request) {
        $domain  = $request->input('domain');
        $type  = $request->input('type');
        $name  = $request->input('name');
        // $fieldIds  = $request->input('fieldIds');
        // $file = $request->file('file');


        $relationsData = $request->input('relations');
        $relationsArray = json_decode($relationsData, true);



        //RELATIONS
        $relations = [];

        foreach ($request->all() as $key => $value) {
            if (strpos($key, 'relations_') === 0) {
                $parts = explode('_', $key);
                array_shift($parts); // Удаляем 'relations'

                $fieldIndex = $parts[1];
                $property = $parts[2];

                if ($property === 'img' && is_a($value, 'Illuminate\Http\UploadedFile')) {
                    // Вместо сохранения файла, сохраняем объект UploadedFile
                    $relations['field'][$fieldIndex][$property] = $value;
                } else if ($property === 'initialValue') {
                    // Вместо сохранения файла, сохраняем объект UploadedFile
                    $relations['field'][$fieldIndex]['value'] = $value;
                } else {
                    // Для других данных просто сохраняем значение
                    $relations['field'][$fieldIndex][$property] = $value;
                }
            }
        }


        $controller = new TemplateController;

        return $controller->setTemplate($domain, $type, $name, $relations);
    });


    //UPDATE
    Route::post('template/{templateId}', function ($templateId, Request $request) {

        $template = [
            'name' => $request['name'],
            'type' => $request['type'],
            'code' => $request['code'],
            'link' => $request['link'],

        ];

        $controller = new TemplateController;

        return $controller->updateTemplate($templateId, $template);
    });
    Route::post('field/{fieldId}', function ($fieldId, Request $request) {

        // $field = [
        //     'number' => $request['number'],
        //     'name' => $request['name'],
        //     'type' => $request['type'],
        //     'code' => $request['code'],
        //     'value' => $request['value'],
        //     'description' => $request['description'],
        //     'bitixId' => $request['bitixId'],
        //     'bitrixTemplateId' => $request['bitrixTemplateId'],
        //     'isGeneral' => $request['isGeneral'],
        //     'isDefault' => $request['isDefault'],
        //     'isRequired' => $request['isRequired'],
        //     'isActive' => $request['isActive'],
        //     'isPlural' => $request['isPlural'],
        //     'isClient' => $request['isClient'],

        // ];

        $controller = new FieldController;

        return $controller->updateField($fieldId, $request);
    });

    //DELETE

    Route::delete('template/{templateId}', function ($templateId) {
        return TemplateController::deleteTemplate($templateId);
    });
    Route::delete('field/{fieldId}', function ($fieldId) {
        return FieldController::deleteField($fieldId);
    });


    ///INFOBLOCKS

    Route::post('infogroups', function (Request $request) {
        $infogroups  = $request->input('infogroups');

        return InfoGroupController::setInfoGroups($infogroups);
    });

    Route::post('infoblocks', function (Request $request) {
        $infoblocks  = $request->input('infoblocks');
        return InfoblockController::setInfoBlocks($infoblocks);
    });

    Route::post('infoblock/{infoblockId}', function ($infoblockId, Request $request) {
        return InfoblockController::updateInfoblock($infoblockId, $request);
    });
    Route::post('infoblock', function (Request $request) {
        return InfoblockController::setInfoblock($request);
    });

    Route::get('infogroups', function () {
        $infogroups  = InfoGroup::all();
        return response([
            'resultCode' => 0,
            'infogroups' =>  $infogroups
        ]);
    });

    Route::get('infoblocks', function () {
        $infoblocks  = Infoblock::all();
        return response([
            'resultCode' => 0,
            'infoblocks' =>  $infoblocks
        ]);
    });

    Route::get('infoblock/{infoblockId}', function ($infoblockId) {
        return InfoblockController::getInfoblock($infoblockId);
    });










    ////FILES


    Route::post('upload/description/general', function (Request $request) {
        return FileController::uploadDescriptionTemplate($request);
    });

    Route::post('portal/template', function (Request $request) {
        return FileController::uploadPortalTemplate($request);
    });






    // Route::get('initial/{parentType}/{parentId}/{entityType}', function ($parentType, $parentId, $entityType) {

    //     return BaseController::initial($entityType, $parentType, $parentId);
    // });

    // Route::get('initial/{entityType}/', function ($entityType) {
    //     return BaseController::initial($entityType);
    // });

    // Route::get('{model}/{modelId}', function ($model, $modelId) {
    //     return BaseController::get($model, $modelId);
    // });
    // Route::get('{model}', function ($model) {
    //     return BaseController::getCollection($model);
    // });
    // Route::post('{parentType}/{parentId}/{entityType}', function ($parentType, $parentId, $entityType, Request $request) {

    //     return BaseController::setOrUpdate($entityType, $parentType, $parentId,  $request);
    // });

    // Route::post('{entityType}/{entityId}', function ($entityType, $entityId, Request $request) {
    //     return BaseController::update($entityType, $entityId,  $request);
    // });
    // Route::post('{entityType}', function ($entityType, Request $request) {
    //     return BaseController::setOrUpdate($entityType, null, null, $request);
    // });

    // Route::delete('{entityType}/{entityId}', function ($entityType, $fileId) {
    //     return BaseController::delete($entityType, $fileId);
    // });

});
