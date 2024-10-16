<?php

use App\Http\Controllers\Admin\InfoblockController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\AppOffer\TemplateController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\BitrixTelephony;
use App\Http\Controllers\DealController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\Front\Konstructor\InfoblockFrontController;
use App\Http\Controllers\Front\Konstructor\TemplateFrontController;
use App\Http\Controllers\PDFDocumentController;
use App\Http\Controllers\PortalController;

use App\Http\Controllers\UserController;
use App\Models\Portal;
use App\Models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use morphos\Russian\Cases;
use morphos\Russian\NounDeclension;
use function morphos\Russian\inflectName;


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




// Route::middleware('auth_hook')->group(function () {
//     Route::post('hooktest', function (Request $request) {
//         return BitrixController::hooktest($request);
//     });
// });
// 'api.key', 
require __DIR__ . '/projects/alfa/alfa_routes.php';
require __DIR__ . '/admin_outer/install.php';

Route::middleware(['ajax.only', 'api.key'])->group(function () {
    require __DIR__ . '/front/konstructor/contract_routes.php';
    //..................................GENERAL FRONT APP ...............................
    Route::post('front/portal', function (Request $request) {
        $domain  = $request->input('domain');
        return PortalController::getFrontPortal($domain);
    });

    ///KONSTRUKTOR OFFER API   ...........................................................
    Route::post('/deal', function (Request $request) {

        return DealController::addDeal($request);
    });

    Route::post('/getDeal', function (Request $request) {

        return DealController::getDeal($request);
    });
    Route::post('/getdeals', function (Request $request) {
        return DealController::getDeals($request->parameter, $request->value);
    });


    Route::get('/infoblocks', function () {
        $controller = new InfoblockFrontController();
        return $controller->getBlocks();
    });



    Route::post('get/document', function (Request $request) {
        $data  = $request->input('data');

        // Log::channel('telegram')->info('APRIL_ONLINE', [
        //     'get/document domain' => $data['domain'],
        //     'get/document userId' => $data['userId'],
        //     'get/document manager' => $data['manager']['NAME'],
        // ]);

        $documentController = new PDFDocumentController;
        $result = $documentController->getDocument($data);


        return $result;
    });

    Route::post('get/contract', function (Request $request) {
        $data  = $request->input('data');

        // Log::channel('telegram')->info('APRIL_ONLINE', [
        //     'get/document domain' => $data['domain'],
        //     'get/document userId' => $data['userId'],
        //     'get/document manager' => $data['manager']['NAME'],
        // ]);

        $documentController = new PDFDocumentController;
        $result = $documentController->getDocument($data);


        return $result;
    });


    Route::post('get/init/contract', function (Request $request) {

        // возвращает форму и 
        // готовит сессию для создания договора
        $data  = $request->input('data');

        $companyId = $data['companyId'];
        $userId = $data['userId'];
        $domain = $data['domain'];
        $templateId = $data['templateId'];
        $currentContract = $data['templateId'];

        $portal = Portal::where('domain', $domain)->first();
        $template = Template::find($templateId);

        // return $result;
    });


    Route::get('templates/{domain}', function ($domain) {
        return TemplateFrontController::getTemplates($domain);
    });




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


    Route::post('konstructor/bitrix/deal/update', function (Request $request) {
        $domain = $request->input('domain');
        $dealId = $request->input('dealId');
        $setDealData = $request->input('setDealData');
        $updateDealInfoblocksData = $request->input('updateDealInfoblocksData');
        $updateDealContractData    = $request->input('updateDealContractData');
        $setProductRowsData = $request->input('setProductRowsData');
        $updateProductRowsData = $request->input('updateProductRowsData');
        $placement = $request->input('placement');

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
            $placement,
            $dealId,
            $setDealData,
            $updateDealInfoblocksData,
            $updateDealContractData,
            $setProductRowsData,
            $updateProductRowsData
        );
        return $data;
    });





    //..................................APRIL HOOK APP ...............................
    Route::post('getportal', function (Request $request) {

        //hook server обращается к этому эндпоинту чтобы получить
        // портал для работы хуков

        $domain  = $request->input('domain');
        return PortalController::getPortal($domain);
    });
});



Route::middleware(['ajax.only'])->group(function () {



    Route::get('/sntm/test', function (Request $request) {
        $apiKey = 'common';



        return APIController::getSuccess(['yo' => $apiKey]);
    });









    Route::post('innerhook/call', function (Request $request) {

        $controller = new BitrixTelephony();
        $result = $controller->statisticGet($request);

        return APIController::getSuccess(['result' => 'success']);
    });







    /////DEALS












    Route::get('portal/{portalId}', function ($portalId) {
        return PortalController::getPortalById($portalId);
    });


    Route::get('portals', function () {
        return PortalController::getPortals();
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




    Route::post('getDescription', function (Request $request) {
        return FileController::getGeneralDescription($request);
    });












    //APRIL OFFER KONSTRUCTOR////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    Route::post('bitrixcompany', function (Request $request) {

        return BitrixController::getCompany($request);
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
    Route::post('bitrix/callingreport', function (Request $request) {

        //userId
        //domain
        //date


        return BitrixController::getCallingTasksReport($request);
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
        // Log::channel('telegram')->error('ONLINE', [
        //     'bitrix/callingtasks/create' => [
        //         $data

        //     ]
        // ]);
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




    //REPORT
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
});



//INSTALL
Route::middleware(['ajax.only', 'ajax.only'])->group(function () {

    // Route::get('/install/deal/fields', function () {
    //     return InstallController::setFieldsfield();
    // });
});



//Users
Route::get('/user/auth', function () {
    return UserController::getAuthUser();
});


Route::get('garavatar/{userId}', function ($userId) {
    $user = User::find($userId)->first();
    return $user->getAvatarUrl();
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
