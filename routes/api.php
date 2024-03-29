<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\BitrixTelephony;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FItemController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\HookController;
use App\Http\Controllers\InfoblockController;
use App\Http\Controllers\InfoGroupController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferMasterController;
use App\Http\Controllers\PDFDocumentController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PriceRowCellController;
use App\Http\Controllers\RqController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UserController;
use App\Http\Resources\UserCollection;
use App\Models\Infoblock;
use App\Models\InfoGroup;
use App\Models\Portal;
use App\Models\PriceRowCell;
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
use App\Models\User;
use App\Services\BitrixDealUpdateService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use morphos\Russian\Cases;
use morphos\Russian\NounDeclension;
use Vipblogger\LaravelBitrix24\Bitrix;

use function morphos\Russian\inflectName;



// Route::middleware('auth_hook')->group(function () {
//     Route::post('hooktest', function (Request $request) {
//         return BitrixController::hooktest($request);
//     });
// });
Route::middleware(['api.key'])->group(function () {




    Route::get('/sntm/test', function (Request $request) {
        $apiKey = 'common';



        return APIController::getSuccess(['yo' => $apiKey]);
    });

    
    Route::post('getportal', function (Request $request) {
        $domain  = $request->input('domain');
        return PortalController::getPortal($domain);
    });





});
Route::post('innerhook/call', function (Request $request) {
    
    $controller = new BitrixTelephony();
    $result = $controller->statisticGet($request);

    return APIController::getSuccess(['result' => 'success']);
});

Route::middleware(['ajax.only'])->group(function () {

    Route::post('get/document', function (Request $request) {
        $data  = $request->input('data');
        Log::channel('console')->info($data);
        $documentController = new PDFDocumentController;
        $result = $documentController->getDocument($data);

        return $result;
    });



    /////DEALS


    Route::post('/deal', function (Request $request) {
        Log::channel('telegram')->error('APRIL_TEST', [
            'getDeal' => [
              
                'domain' => $request['domain'],
                'dealId' => $request['dealId'],

            ]
        ]);
        return DealController::addDeal($request);
    });

    Route::post('/getDeal', function (Request $request) {

        return DealController::getDeal($request);
    });


    Route::post('/getdeals', function (Request $request) {
        return DealController::getDeals($request->parameter, $request->value);
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



    // Route::post('pricerowcells', function (Request $request) {
    //     $pricerowcells  = $request->input('pricerowcells');
    //     return PriceRowCellController::setCells($pricerowcells);
    // });


    // Route::post('pricerowcells', function (Request $request) {
    //     $pricerowcells  = $request->input('pricerowcells');
    //     return PriceRowCellController::setCells($pricerowcells);
    // });


    // Route::get('pricerowcells', function (Request $request) {

    //     return PriceRowCellController::getCells();
    // });


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

        // Пример обработки полученных данных
        // foreach ($relations['field'] as $index => $data) {
        //     // Здесь ваш код для обработки данных, связанных с каждым полем
        // }


        // $relations = $request->file('relations');

        //FILE
        // $file = $request->file('relations_field_0_img_0');
        // Log::info('file', ['file' => $file]);
        // if (json_last_error() !== JSON_ERROR_NONE) {
        //     // Ошибка декодирования JSON
        //     Log::error('JSON decode error: ' . json_last_error_msg());
        //     // Обработка ошибки...
        // }
        // if ($request->hasFile('relations_field_0_img_0')) {
        //     $file = $request->file('relations_field_0_img_0');

        //     // Сохраняем файл в папке 'public' и получаем путь
        //     $filePath = $file->store('public/template/images/test');
        //     // Генерируем URL для доступа к файлу
        //     $fileUrl = Storage::url($filePath);

        //     // Теперь $fileUrl содержит URL к файлу, который можно сохранить в базе данных
        // }
        // return response([
        //     'result' => [
        //         '$domain' => $domain,
        //         'fileUrl' => $fileUrl,
        //         'file' =>  $file,
        //         '$relationsData' => $relationsData
        //     ]


        // ]);
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









    // Route::post('field/set', function (Request $request) {
    //     $templateId  = $request->input('templateId');
    //     $field  = $request->input('field');


    //     return FieldController::createField($templateId, $field);
    // });

    // Route::post('template/update', function (Request $request) {
    //     $domain  = $request->input('domain');
    //     $type  = $request->input('type');
    //     return TemplateController::initialTemplate($type);
    // });




    //RESULT DOCUMENTS
    // Route::get('/pdf', [PDFDocumentController::class, 'generatePDF']);


 










    // Route::post('get/document', function (Request $request) {
    //     $data  = $request->input('data');
    //     $documentController = new DocumentController;
    //     $result = $documentController->getDocument($data);

    //     return $result;
    // });

    // Route::post('get/document', function (Request $request) {
    //     $data  = $request->input('data');
    //     $documentController = new GoogleController;
    //     $result = $documentController->documentCreate($data);

    //     return $result;
    // });
    Route::post('nameCase', function (Request $request) {
        $word  = $request->input('word');
        $wordCase  = $request->input('wordCase');
        $resultName = inflectName($word, $wordCase);

        return response([
            'resultCode' =>  0,
            'nameCase' => $resultName
        ]);
    });

    Route::post('wordCase', function (Request $request) {
        $phrase  = $request->input('word');
        $wordCase  = $request->input('wordCase');
        if (!$phrase || $phrase == '') {
            return APIController::getSuccess(['wordCase' => '']);
        } else {
            $words = explode(' ', $phrase);
            $resultWords = [];
            // $resultposition = NounDeclension::getCase($wordFromFront, $wordCase);
            // $declension = new NounDeclension();
            foreach ($words as $word) {
                $declinedWord  = NounDeclension::getCase($word, Cases::DATIVE); // Используйте DATIVE для дательного падежа



                if (mb_substr($word, 0, 1) === mb_strtoupper(mb_substr($word, 0, 1))) {
                    // Применяем заглавную букву к склоненному слову
                    $declinedWord = mb_strtoupper(mb_substr($declinedWord, 0, 1)) . mb_substr($declinedWord, 1);
                }
                array_push($resultWords, $declinedWord);
                $word = $declinedWord;
            }

            $declinedPhrase = implode(' ', $resultWords);
            return response([
                'resultCode' =>  0,
                'wordCase' => $declinedPhrase,


            ]);
        }
    });



    ///infoblocks for template 
    Route::post('get/infoblocks/description', function (Request $request) {
        $infoblocks  = $request->infoblocks;
        return InfoblockController::getInfoblocksDescription($infoblocks);
    });

    //FOR CLIENT APPP

    Route::post('kp/template/get', function (Request $request) {
        $code  = $request->input('code');

        return TemplateController::getClientTemplate($code);
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



    // ROUTE TESTING BITRIX PROVIDERS

    // Route::post('/april', function (Bitrix $bitrix) {


    //     $portal = Portal::getPortal($bitrix);

    //     if ($portal) {

    //         // $hook = $portal['C_REST_WEB_HOOK_URL'];
    //         $response = BitrixController::connect($bitrix);

    //         return response(['data' => $response]);
    //     } else {

    //         return response(['message' => 'no portal']);
    //     }
    // });








    ////FILES


    Route::post('upload/description/general', function (Request $request) {
        return FileController::uploadDescriptionTemplate($request);
    });

    Route::post('portal/template', function (Request $request) {
        return FileController::uploadPortalTemplate($request);
    });




    //FILES OFFERS



    Route::post('get/offer', function (Request $request) {
        return FileController::getGeneralOffer($request);
    });



    //TODO BITRIX HOOKS - CHANGE STAGES : ////////////////////////////////////
    // Route - хук работающий при смене стадии сделки на всех порталах (!) отдает general или client kp
    // Route::get('/stage/kp', function (Bitrix $bitrix) {
    //     $fields = $bitrix->call('crm.deal.fields');
    //     $profile =  $bitrix->call('profile');
    //     // var_dump($result);
    //     return response(['fields' => $fields,  'profile' => $profile]);
    // });






    //TODO GET DESCRIPTION IN APRIL KP (FROM RESULT COMPONENT)
    Route::post('addGeneralDescriptionTemplate', function (Request $request) {
        //TODO
        //route должен класть(обновлять) шаблон в специальном месте в файловом хранилище
        //вызывать из админки 
    });

    // TODO - в файловом хранилище должна быть у каждого клиента своя папка 



    Route::post('addClientDescriptionTemplate', function (Request $request) {
        // TODO добавляет (обновляет) файл в файловом хранилище в специальной клиентской папке
        // Ставит галочку у Client - что у него кастомный (свой клиентский) шаблон
        // и в следующий раз когда клиент с этим домен будет просить Description File - сервер поймет что надо отдавать клиентский шаблон   


    });

    Route::post('getDescription', function (Request $request) {
        return FileController::getGeneralDescription($request);
    });






    /////////////////////////APRIL_HOOK CONNECT
    //report
    Route::post('bitrix/calling', function (Request $request) {
        $domain = $request->domain;
        $filters = $request->filters;
        return BitrixController::getBitrixCallingStatistics($request);
    });
    Route::post('bitrix/report', function (Request $request) {
        $domain = $request->domain;
        $filters = $request->filters;
        return BitrixController::getReport($request);
    });

    Route::post('bitrix/departament', function (Request $request) {
        return BitrixController::getDepartamentUsers($request);
    });
    Route::post('bitrix/list', function (Request $request) {

        return BitrixController::getList($request);
    });
    Route::post('bitrix/filter', function (Request $request) {

        return BitrixController::getListFilter($request);
    });





    //APRIL OFFER KONSTRUCTOR////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    Route::post('bitrixcompany', function (Request $request) {

        return BitrixController::getCompany($request);
    });

    Route::post('konstructor/bitrix/deal/update', function (Request $request) {
        $domain = $request->input('domain');
        $dealId = $request->input('dealId');
        $setDealData = $request->input('setDealData');
        $updateDealInfoblocksData = $request->input('updateDealInfoblocksData');
        $updateDealContractData    = $request->input('updateDealContractData');
        $setProductRowsData = $request->input('setProductRowsData');
        $updateProductRowsData = $request->input('updateProductRowsData');


        // $service = new BitrixDealUpdateService(
        //     $domain,
        //     $dealId,
        //     $setDealData,
        //     $updateDealInfoblocksData,
        //     $updateDealContractData,
        //     $setProductRowsData,
        //     $updateProductRowsData

        // );
        $controller = new BitrixController;

        $data = $controller->konstructBitrixDealUpdate(
            $domain,
            $dealId,
            $setDealData,
            $updateDealInfoblocksData,
            $updateDealContractData,
            $setProductRowsData,
            $updateProductRowsData
        );
        return $data;
    });



    Route::post('bitrixdeal', function (Request $request) {

        return BitrixController::getDeal($request);
    });





    //calling tasks
    Route::post('bitrix/callingtasks', function (Request $request) {

        //userId
        //domain
        //date


        return BitrixController::getCallingTasks($request);
    });

    Route::post('bitrix/callingtasks/create', function (Request $request) {

        $data = $request->all();
        $controller = new BitrixController();
        $placement = $data['placement']['placement'];
        $placementId = $data['placement']['options']['ID'];
        $crm = null;
        if (strpos($placement, "LEAD") !== false) {
            $crm = "L_" . $placementId;
        } elseif (strpos($placement, "COMPANY") !== false) {
            $crm = "CO_" . $placementId;
        } elseif (strpos($placement, "DEAL") !== false) {
            $crm = "D_" . $placementId;
        }
        // return APIController::getSuccess(['task' => $data]);
        return $controller->createTask(
            $data['domain'],
            $placementId,
            $data['createdBy'],
            $data['responsibility'],
            $data['deadline'],
            $data['name'],
            $crm,
            $data['type'],


        );
    });







    ////////////////////////BASE CONTROLLER      ADMIN



});






// routes/web.php или routes/api.php

// Route::post('/settings', 'SettingsController@index');
// Route::post('/upload', 'FileUploadController@upload');
Route::post('upload', function (Request $request) {
    // return FileController::getFile($request);
});

//Users
Route::get('/user/auth', function () {
    return UserController::getAuthUser();
});


Route::get('garavatar/{userId}', function ($userId) {
    $user = User::find($userId)->first();
    return $user->getAvatarUrl();
});







//////////////////BITRIX

// Route::get('/profile', function (Bitrix $bitrix) {

//     $profile =  $bitrix->call('profile');
//     // var_dump($result);
//     return response(['profile' => $profile]);
// });



// Route::post('/file/write', function (Request $request) {

//     if ($request->hasFile('file')) {
//         $file = $request->file('file');

//         // Проверка на расширение .doc или .docx
//         if ($file->getClientOriginalExtension() === 'doc' || $file->getClientOriginalExtension() === 'docx') {

//             // Сохранение файла на сервере
//             $path = $file->store('public');

//             // Загрузка файла в PHPWord
//             $phpWord = new \PhpOffice\PhpWord\PhpWord()

//             // Редактирование файла
//             // $sections = $phpWord->getSections();
//             // $sectionы = $phpWord->addSection(array('pageNumberingStart' => 1));
//             $data = [
//                 ['name' => 'supply', 'bitrixId' => 'UF_CRM_15168672545'],
//                 // Другие объекты...
//             ];

//             foreach($sections as $section) {
//                 $elements = $section->getElements();

//                 foreach($elements as $element) {
//                     if(method_exists($element, 'getText')) {
//                         $text = $element->getText();

//                         foreach($data as $replace) {
//                             if ($text === $replace['name']) {
//                                 $element->setText($replace['bitrixId']);
//                             }
//                         }
//                     }
//                 }
//             }

//             // Сохранение отредактированного файла
//             $writer = IOFactory::createWriter($phpWord, 'Word2007');
//             $newPath = 'public/edited_' . $file->getClientOriginalName();
//             $writer->save(Storage::path($newPath));

//             // Удаление исходного файла
//             Storage::delete($path);

//             // Отправка ссылки на файл обратно клиенту
//             $response = [
//                 'resultCode'=> 0,
//                 'message' => 'File edited successfully',
//                 'file' => Storage::url($newPath)
//             ];

//             return response($response);
//         }
//     }

//     return response(['resultCode' => 1, 'message' => 'No file uploaded or wrong file type']);
// });

Route::post('createAprilTemplate', function (Request $request) {
    return FileController::processFields($request);
});




Route::post('/file', function (Request $request) {


    if ($request->hasFile('file')) {
        $file = $request->file('file');
        // $domain = $request->file('file');

        // сохраняем файл на сервере
        $filename = $file->getClientOriginalName();
        // $filePath = public_path('uploads/' . $filename);
        // $file->move(public_path('uploads'), $filename);

        // возвращаем ссылку на файл клиенту
        // $responseData = ['resultCode' => 0, 'message' => 'hi friend', 'file' => url('uploads/' . $filename)];
        // $path = $request->file('file')->store('test');


        // Storage::disk('public')->put($filename, 'test');
        $path = $file->storeAs('public', $filename);


        $responseData = ['resultCode' => 0, 'message' => 'hi friend', 'fileName' => $filename];
        // $response = response()->json($responseData);

        // ждем 5 секунд и удаляем файл
        // sleep(100);
        // if (file_exists($filePath)) {
        //     unlink($filePath);
        // }

        return response()->json($responseData);
    }
});





//Users


Route::get('/user/auth', function () {
    return UserController::getAuthUser();
});


//

Route::post('/sanctum/token', TokenController::class);


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/tokens/create', function (Request $request) {
    $token = $request->user()->createToken($request->token_name);

    return ['token' => $token->plainTextToken];
});


// export enum ResultCodesEnum {
//     Error = 1,
//     Success = 0
// }

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