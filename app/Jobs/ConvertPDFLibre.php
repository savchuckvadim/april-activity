<?php

namespace App\Jobs;

use App\Http\Controllers\ALogController;
use App\Http\Controllers\BitrixController;
use App\Services\BitrixDealUpdateService;
use Illuminate\Bus\Queueable;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class ConvertPDFLibre implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    protected $domain;
    protected $hook;
    protected $hash;
    protected $dealId;
    protected $fullOutputFilePath;
    protected $outputFileName;

    public function __construct(
        $domain,
        $hook,
        $hash,
        $dealId,
        $fullOutputFilePath,
        $outputFileName,

    ) {
        $this->domain =  $domain;
      
        $this->dealId =  $dealId;
        $this->fullOutputFilePath =  $fullOutputFilePath;
        $this->outputFileName =  $outputFileName;
        $this->hook =  $hook;
        $this->hash =  $hash;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        putenv('HOME=/tmp');
        putenv('JAVA_TOOL_OPTIONS=-Djava.awt.headless=true');

        $docxFile = $this->fullOutputFilePath;
        $pdfFilePath = str_replace('.docx', '.pdf', $docxFile);

        // $command = "/usr/bin/libreoffice --headless --convert-to pdf --outdir " . escapeshellarg(dirname($pdfFilePath)) . " " . escapeshellarg($docxFile);
        $command = "/usr/bin/libreoffice --headless --nologo --nofirststartwizard --invisible --convert-to pdf --outdir " 
        . escapeshellarg(dirname($pdfFilePath)) 
        . " " . escapeshellarg($docxFile);
       
        exec($command . " 2>&1", $output, $returnCode);
        ALogController::push("LibreOffice Output: " . implode("\n", $output), [$output]); // Логируем вывод команды

        if ($returnCode !== 0) {
            throw new \Exception("Ошибка при конвертации DOCX в PDF");
        }

        // // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ

        $link =   route('download-supply-report', ['domain' => $this->domain,  'hash' => $this->hash, 'filename' =>  basename($pdfFilePath)]);
        
        $method = '/crm.timeline.comment.add';

        $url = $this->hook . $method;

        $message = '<a href="' . $link . '" target="_blank">' . 'Отчет_о_продаже'. '</a>';

        $fields = [
            "ENTITY_ID" => $this->dealId,
            "ENTITY_TYPE" => 'deal',
            "COMMENT" => $message
        ];
        $data = [
            'fields' => $fields
        ];
        $responseBitrix = Http::get($url, $data);
        ALogController::push(
            "LibreOffice Job push to bx Output: ", 
            ['responseBitrix' => $responseBitrix, 'domain' => $this->domain]); // Логируем вывод команды

    }
}
