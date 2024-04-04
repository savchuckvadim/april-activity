<?php


use App\Http\Controllers\APIController;
use App\Http\Controllers\BitrixController;
use App\Http\Controllers\BitrixTelephony;
use App\Http\Controllers\DealController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\InfoblockController;
use App\Http\Controllers\PDFDocumentController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use Illuminate\Support\Facades\Log;
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
Route::middleware(['ajax.only'])->group(function () {
    // Route::middleware(['api.key, ajax.only'])->group(function () {



    Route::get('/sntm/test', function (Request $request) {
        $apiKey = 'common';



        return APIController::getSuccess(['yo' => $apiKey]);
    });


    Route::post('getportal', function (Request $request) {
        $domain  = $request->input('domain');
        return PortalController::getPortal($domain);
    });






    Route::post('innerhook/call', function (Request $request) {

        $controller = new BitrixTelephony();
        $result = $controller->statisticGet($request);

        return APIController::getSuccess(['result' => 'success']);
    });



    Route::post('get/document', function (Request $request) {
        $data  = $request->input('data');

        Log::channel('telegram')->error('APRIL_TEST', [
            'Get Document' => [

                'all' => $data,


            ]
        ]);
        if (isset($data['placement']) && isset($data['companyId'])) {
            Log::channel('telegram')->error('APRIL_TEST', [
                'Get Document' => [

                    'placement' => $data['placement'],
                    'companyId' => $data['companyId'],

                ]
            ]);
        }
        if (isset($data['userId'])) {
            Log::channel('telegram')->error('APRIL_TEST', [
                'Get Document' => [

                    'userId' => $data['userId'],
                  

                ]
            ]);
        }

        $documentController = new PDFDocumentController;
        $result = $documentController->getDocument($data);


        return $result;
    });



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









    Route::get('portal/{portalId}', function ($portalId) {
        return PortalController::getPortalById($portalId);
    });


    Route::get('portals', function () {
        return PortalController::getPortals();
    });







    Route::get('templates/{domain}', function ($domain) {
        return TemplateController::getTemplates($domain);
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
        Log::channel('telegram')->error('ONLINE', [
            'bitrix/callingtasks/create' => [
                $data

            ]
        ]);
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
