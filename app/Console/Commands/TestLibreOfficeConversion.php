<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpWord\TemplateProcessor;

class TestLibreOfficeConversion extends Command
{
    // Имя команды для консоли
    protected $signature = 'test:libreoffice';
    protected $description = 'Тестовая конвертация DOCX в PDF с помощью LibreOffice';

    public function handle()
    {
        $this->info('Начало теста конвертации DOCX в PDF...');

        // Создание тестового DOCX файла
        // $templateProcessor = new TemplateProcessor(storage_path('app/public/test/sales_report.docx'));
        $outputFilePath = storage_path('app/public/test/sample.docx');
        // $templateProcessor->saveAs($outputFilePath);

        $this->info("DOCX файл создан: $outputFilePath");

        // Конвертация DOCX в PDF
        $pdfFilePath = str_replace('.docx', '.pdf', $outputFilePath);
        $command = "/usr/bin/libreoffice --headless --convert-to pdf --outdir " . escapeshellarg(dirname($pdfFilePath)) . " " . escapeshellarg($outputFilePath);
        

        exec($command . " 2>&1", $output, $returnCode);

        // Логирование вывода команды
        $this->info("LibreOffice Output:\n" . implode("\n", $output));

        if ($returnCode !== 0) {
            $this->error('Ошибка при конвертации DOCX в PDF');
            return 1; // Возвращаем код ошибки
        }

        $this->info("PDF файл успешно создан: $pdfFilePath");

        return 0; // Возвращаем успешный код
    }
}
