<?php

namespace App\Http\Controllers\Yandex;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Services\Yandex\TranscribationService;
use Illuminate\Http\Request;

class TranscribationController extends Controller
{

    public function getTranscribation(Request $request)
    {
        try {
            ini_set('max_execution_time', 900); // 5 минут

            $fileUrl = $request->fileUrl;
            $fileName = $request->fileName;
            $yandexService = new TranscribationService();
            $transcription = $yandexService->transcribe($fileUrl, $fileName);
            return APIController::getSuccess(['transcription' => $transcription]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ]);
        }
    }
}
