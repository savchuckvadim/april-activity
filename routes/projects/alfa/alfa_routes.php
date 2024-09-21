<?php

use App\Http\Controllers\APIController;
use App\Http\Controllers\Front\Konstructor\ContractController;
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


    Route::get('/specification', function (Request $request) {
        $alfapath = 'app/public/projects/alfacontracts/ppk';

        // Используем storage_path только один раз
        $fullPath = storage_path($alfapath . '/specification.docx');
        $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($fullPath);
        $documentNumber = $request->documentNumber;
        $documentCreateDate = $request->documentCreateDate;
        $listItems = [
            [
                "ID" => "30020",
                "IBLOCK_ID" => "48",
                "NAME" => "Участник семинара №1",
                "IBLOCK_SECTION_ID" => null,
                "CREATED_BY" => "502",
                "BP_PUBLISHED" => "Y",
                "CODE" => null,
                "PROPERTY_192" => ["145198" => "1812"],
                "PROPERTY_204" => ["145200" => "[]СЕ Семинар"]

            ],

            [
                "ID" => "30020",
                "IBLOCK_ID" => "48",
                "NAME" => "Участник семинара №2",
                "IBLOCK_SECTION_ID" => null,
                "CREATED_BY" => "502",
                "BP_PUBLISHED" => "Y",
                "CODE" => null,
                "PROPERTY_192" => ["145198" => "1812"],
                "PROPERTY_204" => ["145200" => "[]СЕ Семинар"]

            ],
        ];
        $persons = [];
        foreach ($listItems as $key => $listItem) {
            $person = [
                'personNumber' => $key + 1 .'. ',
                'person' => $listItem['NAME'],

            ];
            foreach ($listItem['PROPERTY_204'] as $key => $value) {
                $person['product'] = $value;
            }
            array_push($persons, $person);
        }
        $documentNumber = 'ТЕСТ НОМЕР ДОКУМЕНТА';
        $documentCreateDate = 'ТЕСТ ДАТА ДОКУМЕНТА';

        $companyName = 'ТЕСТ НАЗВАНИЕ КОМПАНИИ';
        $position = 'ДИРЕКТОР';
        $director = 'ТЕСТ ИМЯ РУКОВОДИТЕЛЯ';
        $templateProcessor->setValue('documentNumber', $documentNumber);
        $templateProcessor->setValue('documentCreateDate', $documentCreateDate);
        $templateProcessor->setValue('companyName', $companyName);
        $templateProcessor->setValue('position', $position);
        $templateProcessor->setValue('director', $director);

        $templateProcessor->cloneRowAndSetValues('personNumber', $persons);
        // $templateProcessor->cloneRowAndSetValues('person', $persons);
        // $templateProcessor->cloneRowAndSetValues('product', $persons);

        // foreach ($persons as $key => $person) {
        //     $templateProcessor->cloneRow('personNumber', $key);
        //     // $templateProcessor->cloneRow('person', $person['NAME']);
        // }

        $outputFileName = 'Приложение к договору_заполнено.docx';
        $outputFilePath = storage_path('app/public/projects/alfacontracts/ppk/documents/' . $outputFileName);

        // Создаём директорию, если она не существует
        $outputDir = dirname($outputFilePath);
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $templateProcessor->saveAs($outputFilePath);

        // Возвращаем ссылку на документ
        $url = Storage::url('projects/alfacontracts/ppk/documents/' . $outputFileName);
        // return APIController::getSuccess([
        //     'link' => $url
        // ]);

        return response()->download($outputFilePath, $outputFileName);
    });
});
