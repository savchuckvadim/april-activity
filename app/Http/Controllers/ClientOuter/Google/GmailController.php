<?php

namespace App\Http\Controllers\ClientOuter\Google;

use App\Http\Controllers\Controller;
use App\Services\Gmail\GmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GmailController extends Controller
{
    protected $gmailService;

    public function __construct(GmailService $gmailService)
    {
        $this->gmailService = $gmailService;
    }

    public function fetchEmails()
    {
        $zipFiles = $this->gmailService->getMails();

        if (count($zipFiles) > 0) {
            $zip = $zipFiles[count($zipFiles) - 1];
            return Response::make($zip['content'], 200, [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $zip['filename'] . '"',
            ]);
        }

        // if (count($zipFiles) > 1) {
        //     $combinedZip = $this->gmailService->combineZipFiles($zipFiles);

        //     return Response::streamDownload(function () use ($combinedZip) {
        //         echo $combinedZip;
        //     }, 'combined_reports.zip', [
        //         'Content-Type' => 'application/zip'
        //     ]);
        // }

        return response()->json(['message' => 'Нет новых ZIP-файлов для загрузки.']);
    }
}
