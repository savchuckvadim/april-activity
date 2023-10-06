<?php

use App\Http\Controllers\BitrixController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\LinkController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OfferMasterController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UserController;
use App\Http\Resources\UserCollection;
use App\Models\Portal;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Vipblogger\LaravelBitrix24\Bitrix;

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


Route::middleware([\Fruitcake\Cors\HandleCors::class])->group(function () {


    /////DEALS


    Route::post('/deal', function (Request $request) {
        return DealController::addDeal($request);
    });

    Route::post('/getDeal', function (Request $request) {
        return DealController::getDeal($request);
    });


    Route::post('/getdeals', function (Request $request) {
        return DealController::getDeals($request->parameter, $request->value);
    });


    //////PORTAL

    Route::post('getportal', function (Request $request) {
        $domain  = $request->input('domain');
        return Portal::getPortal($domain);
    });
    Route::get('portals', function () {
        
        return Portal::getPortals();
    });
    Route::post('portal', function (Request $request) {
        $domain  = $request->input('domain');
        $key = $request->input('key'); //placement key
        $clientId  = $request->input('clientId');
        $secret = $request->input('clientSecret');
        $hook = $request->input('hook');
        return Portal::setPortal($domain, $key, $clientId, $secret, $hook);
    });




    Route::get('/profile', function (Bitrix $bitrix) {

        $profile =  $bitrix->call('profile');
        // var_dump($result);
        return response(['profile' => $profile]);
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
