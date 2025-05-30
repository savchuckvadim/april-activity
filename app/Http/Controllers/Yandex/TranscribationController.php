<?php

namespace App\Http\Controllers\Yandex;

use App\Http\Controllers\APIController;
use App\Http\Controllers\Controller;
use App\Jobs\TranscribeAudioJob;
use App\Services\Yandex\TranscribationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class TranscribationController extends Controller
{

    public function getTranscribation(Request $request)
    {
        try {


            $fileUrl = $request->fileUrl;
            $fileName = $request->fileName;
            $userId = $request->userId;
            $domain = $request->domain;
            $taskId = md5("{$domain}_{$userId}_{$fileName}");
            $redisKey = "transcription:{$taskId}:status"; // "processing", "done", "error"

            // Проверяем, есть ли уже такая задача в Redis
            if (Redis::exists($redisKey)) {
                return APIController::getSuccess(['result' => [
                    'taskId' => $taskId,
                    'status' => 'already_processing'
                ]]);
            }

            // Записываем в Redis, что задача выполняется (ставим TTL на 1 час)
            Redis::setex($redisKey, 3600, 'processing');

            // Отправляем в очередь
            dispatch(new TranscribeAudioJob(
                $fileUrl,
                $fileName,
                $taskId,
                $domain,
                $userId
            ));

            return APIController::getSuccess(['result' => [
                'taskId' => $taskId,
                'status' => 'started'
            ]]);
        } catch (\Throwable $th) {
            return APIController::getError($th->getMessage(), [
                'message'   => $th->getMessage(),
                'file'      => $th->getFile(),
                'line'      => $th->getLine(),
                'trace'     => $th->getTraceAsString(),
            ]);
        }
    }

    public function getTranscriptionResult($taskId)
    {
        $status = Redis::get("transcription:{$taskId}:status");
        $error = Redis::get("transcription:{$taskId}:error");
        $text = Redis::get("transcription:{$taskId}:text");
        $data = [
            'taskId' => $taskId,
            'status' => $status,
            'text' => $text,
        ];
        if (!$status) {
            return APIController::getError('
            Task not found or expired.', [
                'taskId' => $taskId,
                'status' => 'not_found',
                'text' => $text
            ]);
        }

        // Если обработка завершена, отдаем текст
        if ($status === 'done') {
            Redis::del([
                "transcription:{$taskId}:status",
                "transcription:{$taskId}:error",
                "transcription:{$taskId}:text",
            ]);
            return APIController::getSuccess(['result' => $data]);
        }

        // Если ошибка, отдаем ошибку
        if ($status === 'error') {
            Redis::del([
                "transcription:{$taskId}:status",
                "transcription:{$taskId}:error",
                "transcription:{$taskId}:text",
            ]);
            return APIController::getError($error ?: 'Unknown error', ['result' => $data]);
        }

        // Если статус все еще "processing", проверяем в Yandex


        // Если `done: false`, просто возвращаем "processing"
        return APIController::getSuccess(['result' => $data]);
    }
}
