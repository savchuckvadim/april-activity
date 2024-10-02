<?php

use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\APIController;
use App\Http\Controllers\Front\Konstructor\ContractController;
use App\Http\Controllers\PortalController;
use App\Models\Portal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::prefix('alfa')->group(function () {

    // Route::prefix('contract')->group(function () {
    //     Route::post('init', [ContractController::class, 'frontInit']);
    //     Route::post('/', [ContractController::class, 'getDocument']);



    // });
    Route::post('/specification', function (Request $request) {
        $alfapath = 'app/public/projects/alfacontracts/ppk';
        $data = $request->all();
        Log::channel('telegram')->info('ONLINE ALFA', [
            'yo online' => 'yo'
        ]);
        Log::channel('telegram')->info('ONLINE ALFA', [
            'data online' => $data
        ]);
        // Используем storage_path только один раз
        $fullPath = storage_path($alfapath . '/specification.docx');
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);
        $documentNumber = $request->documentNumber;
        $documentCreateDate = $request->documentCreateDate;
        $persons = $request->persons;
        $companyName = $request->companyName;
        $position = $request->position;
        $director = $request->director;




        $templateProcessor->setValue('documentNumber', $documentNumber);
        $templateProcessor->setValue('documentCreateDate', $documentCreateDate);
        $templateProcessor->setValue('companyName', $companyName);
        $templateProcessor->setValue('position', $position);
        $templateProcessor->setValue('director', $director);

        $templateProcessor->cloneRowAndSetValues('personNumber', $persons);

        // foreach ($persons as $key => $person) {
        //     $templateProcessor->cloneRowAndSetValues('personNumber', $key);
        // }
        $hash = md5(uniqid(mt_rand(), true));
        $outputFileName = 'Приложение к договору.docx';
        $outputFilePath = storage_path('app/public/projects/alfacontracts/ppk/documents/' . $hash . '/' . $outputFileName);

        $outputDir = dirname($outputFilePath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $templateProcessor->saveAs($outputFilePath);
        $url = route('download-document', ['hash' => $hash, 'filename' => $outputFileName]);

        // Возвращаем ссылку на документ
        // $url = Storage::url('projects/alfacontracts/ppk/documents/' . $outputFileName);
        return APIController::getSuccess([
            'link' => $url
        ]);
    });




    Route::get('/stamps/{domain}', function ($domain) {
        $portal = Portal::where('domain', $domain)->first();
        $providers = $portal->providers;
        $provider = $providers[0];
        $rq = $provider->rq;
        $signatures = $rq->signatures;
        $stamps = $rq->stamps;

        $stampsData = [

            'stamp' => '',
            'signature' => '',
            'signature_accountant' => '',
            'director' =>  $rq['director'],
            'accountant' =>  $rq['accountant'],

        ];

        // Кодируем содержимое файлов в Base64 для включения в ответ
        if (!empty($stamps)) {
            $filePath = storage_path('app/' . $stamps[0]['path']);
            if (file_exists($filePath)) {
                $stampsData['stamp'] = base64_encode(file_get_contents($filePath));
            }
        }

        if (!empty($signatures)) {
            foreach ($signatures as $key => $signature) {
                $filePath = storage_path('app/' . $signature['path']);
                if (file_exists($filePath)) {
                    if ($signature['code'] !== 'signature_accountant') {
                        $stampsData['signature'] = base64_encode(file_get_contents($filePath));
                    } else {
                        $stampsData['signature_accountant'] = base64_encode(file_get_contents($filePath));
                    }
                }
            }
        }
        return APIController::getSuccess(['result' => $stampsData]);

    });
});
