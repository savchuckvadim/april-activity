<?php

use App\Http\Controllers\AgentController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\FieldController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\FItemController;
use App\Http\Controllers\InfoblockController;
use App\Http\Controllers\InfoGroupController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferMasterController;
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
use GuzzleHttp\Psr7\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use morphos\Russian\Cases;
use morphos\Russian\NounDeclension;
use Vipblogger\LaravelBitrix24\Bitrix;

use function morphos\Russian\inflectName;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/users', function (Request $request) {

        $itemsCount = $request->query('count');
        $paginate = User::paginate($itemsCount);
        $collection = new UserCollection($paginate);

        return $collection;
    });


    Route::delete('/users/{userId}', function ($userId) {
        return UserController::deleteUser($userId);
    });

    Route::post('/users/add', function (Request $request) {
        return UserController::addUser($request);
    });
});

// Route::middleware('auth_hook')->group(function () {
//     Route::post('hooktest', function (Request $request) {
//         return BitrixController::hooktest($request);
//     });
// });


Route::middleware([\Fruitcake\Cors\HandleCors::class, 'ajax.only'])->group(function () {


    /////DEALS
    Log::info('in_middle', ['in_middle' => true]);

    Route::post('/deal', function (Request $request) {
        return DealController::addDeal($request);
    });

    Route::post('/getDeal', function (Request $request) {
        return DealController::getDeal($request);
    });


    Route::post('/getdeals', function (Request $request) {
        return DealController::getDeals($request->parameter, $request->value);
    });

    //////////////////////////////CLIENTS
    //////PORTAL

    Route::post('getportal', function (Request $request) {
        $domain  = $request->input('domain');
        return PortalController::getPortal($domain);
    });
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
    Route::get('template/{templateId}/fields', function ($templateId) {
        return FieldController::getFields($templateId);
    });
    Route::get('field/{fieldId}/items', function ($fieldId) {
        return FItemController::getFitems($fieldId);
    });

    //// no specific
    Route::get('templates', function () {
        return TemplateController::getAllTemplates();
    });


    Route::get('templates/{domain}', function ($domain) {
        return TemplateController::getTemplates($domain);
    });



    Route::get('fields/{templateId}', function ($templateId) {
        return FieldController::getFields($templateId);
    });
    Route::get('fields', function () {
        return FieldController::getAllFields();
    });
    Route::get('items/{fieldId}', function ($fieldId) {
        return FItemController::getFitems($fieldId);
    });


    //GET ITEM
    //// no specific
    Route::get('template/{templateId}', function ($templateId) {
        return TemplateController::getTemplate($templateId);
    });

    Route::get('field/{fieldId}', function ($fieldId) {
        return FieldController::getField($fieldId);
    });
    Route::get('item/{fitemId}', function ($fitemId) {
        return FItemController::getFitem($fitemId);
    });



    //INITIAL SET
    //// specific
    Route::get('initial/template/{templateId}/field', function ($templateId) {
        return FieldController::getInitialField($templateId);
    });
    Route::get('initial/field/{fieldId}/item', function () {
        return FItemController::getInitialFitem();
    });


    //// no specific

    Route::get('initial/template', function (Request $request) {
        $domain = $request->query('domain');

        return TemplateController::initialTemplate($domain);
    });

    Route::get('initial/field', function () {
        return FieldController::getInitialField();
    });
    Route::get('initial/item', function () {
        return FItemController::getInitialFitem();
    });


    //SET 
    //// specific
    Route::post('template/{templateId}/field', function ($templateId, Request $request) {
        $fieldData = [
            'name' => $request['name'],
            'type' => $request['type'],
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


    //// no specific

    Route::post('template', function (Request $request) {
        $domain  = $request->input('domain');
        $type  = $request->input('type');
        $name  = $request->input('name');
        // $fieldIds  = $request->input('fieldIds');
        // $file = $request->file('file');

        Log::info('in_middle', ['in_middle' => $request->all()]);
        $relationsData = $request->input('relations');
        $relationsArray = json_decode($relationsData, true);
        Log::info('relationsArray', ['relationsArray' => $relationsArray]);


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
            '$words' => $words,
            '$resultWords' => $resultWords
        ]);
    });







    //RESULT DOCUMENTS

    Route::post('get/document', function (Request $request) {
        $data  = $request->input('data');
        // $templateId  = $data('templateId');
        $template  = $data['template'];

        $domain  =  $template['portal'];
        $userId  = $data['userId'];
        $price  = $data['price'];
        $infoblocks  = $data['infoblocks'];

        $provider  = $data['provider'];
        $recipient  = $data['recipient'];

        $result = FileController::getDocument(
            $template,
            $domain,
            $userId,
            $price,
            $infoblocks,
            $provider,
            $recipient
        );
        // $wordCase  = $request->input('wordCase');
        // $resultName = inflectName($word, $wordCase);

        return $result;
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

    Route::post('/april', function (Bitrix $bitrix) {


        $portal = Portal::getPortal($bitrix);

        if ($portal) {

            // $hook = $portal['C_REST_WEB_HOOK_URL'];
            $response = BitrixController::connect($bitrix);

            return response(['data' => $response]);
        } else {

            return response(['message' => 'no portal']);
        }
    });








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
    Route::get('/stage/kp', function (Bitrix $bitrix) {
        $fields = $bitrix->call('crm.deal.fields');
        $profile =  $bitrix->call('profile');
        // var_dump($result);
        return response(['fields' => $fields,  'profile' => $profile]);
    });






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
});






// routes/web.php или routes/api.php

// Route::post('/settings', 'SettingsController@index');
// Route::post('/upload', 'FileUploadController@upload');
Route::post('upload', function (Request $request) {
    return FileController::getFile($request);
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

Route::get('/profile', function (Bitrix $bitrix) {

    $profile =  $bitrix->call('profile');
    // var_dump($result);
    return response(['profile' => $profile]);
});



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
