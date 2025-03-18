<?php

namespace App\Jobs;

use App\Http\Controllers\ALogController;
use App\Services\Yandex\TranscribationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TranscribeAudioJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected string $fileUrl;
    protected string $fileName;
    protected string $taskId;
    protected string $domain;
    protected string $userId;

    public function __construct(
        $fileUrl,
        $fileName,
        $taskId,
        $domain,
        $userId,

    ) {
        $this->fileUrl = $fileUrl;
        $this->fileName = $fileName;
        $this->taskId = $taskId;
        $this->domain = $domain;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {


            ini_set('max_execution_time', 900); // 15 минут
            ALogController::push(
                'transribe job',
                ['message' => "Запущена транскрибация для taskId: {$this->taskId}"]
            );
            $service = new TranscribationService(
                $this->taskId,
                $this->domain,
                $this->userId,

            );
            $transcription = $service->transcribe(
                $this->fileUrl,
                $this->fileName
            );
            ALogController::push(
                'transribe job',
                ['transcription result' => $transcription]
            );
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            ALogController::push('TranscribeAudioJob', ['error' => $e->getMessage(), 'from' => 'TranscribeAudioJob']);
          
        }
    }
}
