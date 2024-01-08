<?php

namespace App\Http\Controllers;

use App\Models\Infoblock;
use Ramsey\Uuid\Uuid;
use PhpOffice\PhpWord\Shared\Converter;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function getDocument($data)
    {
        $infoblocksOptions = [
            'description' => $data['infoblocks']['description']['current'],
            'style' => $data['infoblocks']['style']['current'],
        ];
        $complect = $data['complect'];

        $templateType = $data['template']['type'];


        //result document
        $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/' . $data['userId']);
        $uid = Uuid::uuid4()->toString();
        $resultFileName = $templateType . '_' . $uid . '.docx';



        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->addParagraphStyle('Heading2', ['alignment' => 'center']);


        //стиль страницы 
        $sectionStyle = array(
            'pageSizeW' => Converter::inchToTwip(8.5), // ширина страницы
            'pageSizeH' => Converter::inchToTwip(11),   // высота страницы
            'marginLeft' => Converter::inchToTwip(0.5),
            'marginRight' => Converter::inchToTwip(0.5),
            'lang' => 'ru-RU'
        );
        $languageEnGbStyle = array('lang' => 'ru-RU');

        $section = $phpWord->addSection($sectionStyle);
        $testInfoblocks = $this->getInfoblocks($infoblocksOptions, $complect, $section);

        // //СОХРАНЕНИЕ ДОКУМЕТА
        // $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        // $objWriter->save($resultPath . '/' . $resultFileName);

        // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ
        // $link = asset('storage/clients/' . $data['domain'] . '/documents/' . $data['userId'] . '/' . $resultFileName);

        return APIController::getSuccess([
            'data' => $data,
            // 'link' => $link,
            'testInfoblocks' => $testInfoblocks
        ]);
    }

    protected function getInfoblocks($infoblocksOptions, $complect, $section)
    {
        $res = [];
        foreach ($complect as $group) {
            // $section->addTextBreak(1);
            // $section->addText($group['groupsName']);
            // $section->addTextBreak(1);
            array_push($res, $group);
            // foreach ($group['value'] as $infoblock) {
            //     $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();

                // $section->addText($currentInfoblock['name']);
                // $section->addTextBreak(1);
                // $section->addText($currentInfoblock['shortDescription']);
                // $section->addTextBreak(1);
            // }
        }


        return $res;
    }
}
