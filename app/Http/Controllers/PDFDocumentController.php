<?php

namespace App\Http\Controllers;

use App\Models\Counter;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

class PDFDocumentController extends Controller
{
    public function getDocument($data)
    {
        if ($data &&  isset($data['template'])) {
            $template = $data['template'];
            if ($template && isset($template['id'])) {


                $templateId = $template['id'];
                $domain = $data['template']['portal'];
                $dealId = $data['dealId'];
                $providerRq = $data['provider']['rq'];
                $isTwoLogo = false;
                if ($providerRq) {
                    if (isset($providerRq['logos'])) {
                        if (count($providerRq['logos']) > 1) {
                            $isTwoLogo = true;
                        }
                    }
                }


                //document number
                $documentNumber = CounterController::getCount($templateId);

                $headerData  = $this->getHeaderData($providerRq, $isTwoLogo);


                //ГЕНЕРАЦИЯ ДОКУМЕНТА
                $pdf = Pdf::loadView('pdf.offer', ['headerData' =>  $headerData]);







                // //СОХРАНЕНИЕ ДОКУМЕТА
                $uid = Uuid::uuid4()->toString();
                $shortUid = substr($uid, 0, 4); // Получение первых 4 символов

                $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/' . $data['userId']);


                if (!file_exists($resultPath)) {
                    mkdir($resultPath, 0775, true); // Создать каталог с правами доступа
                }

                // Проверить доступность каталога для записи
                if (!is_writable($resultPath)) {
                    throw new \Exception("Невозможно записать в каталог: $resultPath");
                }
                $resultFileName = $documentNumber . '_' . $shortUid . '.pdf';
                $pdf->save($resultPath . '/' . $resultFileName);

                // $objWriter->save($resultPath . '/' . $resultFileName);

                // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ

                $link = asset('storage/clients/' . $domain . '/documents/' . $data['userId'] . '/' . $resultFileName);
                // $link = $pdf->download($resultFileName);
                // return APIController::getSuccess([
                //     'price' => $price,
                //     'link' => $link,
                //     'documentNumber' => $documentNumber,
                //     'counter' => $counter,

                // ]);


                //BITRIX
                // $this->setTimeline($domain, $dealId, $link, $documentNumber);
                // $bitrixController = new BitrixController();
                // $response = $bitrixController->changeDealStage($domain, $dealId, "PREPARATION");

                return APIController::getSuccess([
                    // 'price' => $price,
                    'link' => $link,
                    // 'documentNumber' => $documentNumber,
                    // 'counter' => $counter,

                ]);
            }
        }
    }


    protected function getHeaderData($providerRq, $isTwoLogo)
    {

        $headerData = [
            'isTwoLogo' => $isTwoLogo,
            'rq' => '',
            'logo_1' => null,
            'logo_2' => null,
        ];
        $rq = '';
        if (!$isTwoLogo) {
            $rq = $providerRq['fullname'];
            if ($providerRq['inn']) {
                $rq = $rq . ', ИНН: ' . $providerRq['inn'] . ', ';
            }
            if ($providerRq['kpp']) {
                $rq = $rq . ', КПП: ' . $providerRq['kpp'] . ', ';
            }

            $rq = $rq . ', ' . $providerRq['primaryAdresss'];
            if ($providerRq['phone']) {
                $rq = $rq . ', ' . $providerRq['phone'];
            }
            if ($providerRq['email']) {
                $rq = $rq . ', ' . $providerRq['email'];
            }
        } else {


            if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos']) && count($providerRq['logos']) > 1) {
                $fullPath2 = storage_path('app/' .  $providerRq['logos'][1]['path']);
                $headerData['logo_2'] = $fullPath2;
            }
        }


        if (isset($providerRq['logos']) && is_array($providerRq['logos']) && !empty($providerRq['logos'])) {
            $fullPath1 = storage_path('app/' .  $providerRq['logos'][0]['path']);
            $headerData['logo_1'] =  $fullPath1;
        }
        $headerData['rq'] = $rq;
        return $headerData;
    }
}
