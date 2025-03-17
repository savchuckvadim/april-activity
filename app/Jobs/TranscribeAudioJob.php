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
    protected  $service;
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
        $this->service = new TranscribationService(
            $taskId,
            $domain,
            $userId,
    
        );
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('max_execution_time', 900); // 15 минут
        ALogController::push(
            'transribe job',
            ['message' => "Запущена транскрибация для taskId: {$this->taskId}"]
        );

        $transcription = $this->service->transcribe(
            $this->fileUrl,
            $this->fileName
        );
    }
}
