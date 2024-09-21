<?php

use App\Http\Controllers\APIController;
use App\Http\Controllers\Front\Konstructor\ContractController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;


Route::prefix('alfa')->group(function () {

    // Route::prefix('contract')->group(function () {
    //     Route::post('init', [ContractController::class, 'frontInit']);
    //     Route::post('/', [ContractController::class, 'getDocument']);



    // });
    Route::post('/specification', function (Request $request) {
        $alfapath = 'app/public/pojects/alfacontracts/ppk';
        $fullPath = storage_path($alfapath . '/specification');
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

     
        foreach ($persons as $key => $person) {
            $templateProcessor->cloneRow('personNumber', $key);
        }

        $fileName = 'documents/Приложение к договору.docx';
        $filePath = storage_path($alfapath . '/' . $fileName);
        $templateProcessor->saveAs($filePath);
        $url = Storage::url($fileName);
        return APIController::getSuccess([
            'link' => $url
        ]);
    });
});


