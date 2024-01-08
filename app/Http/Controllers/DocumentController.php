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
            'lang' => 'ru-RU',
            'heading' => ['bold' => true, 'size' => 16, 'name' => 'Arial'],
            'text' => ['size' => 12, 'name' => 'Arial'],
            'textBold' => ['size' => 12, 'name' => 'Arial', 'bold' => true]
        );
        // $languageEnGbStyle = array('lang' => 'ru-RU');

        $section = $phpWord->addSection($sectionStyle);
        $section = $this->getInfoblocks($infoblocksOptions, $complect, $section, $sectionStyle);

        // //СОХРАНЕНИЕ ДОКУМЕТА
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($resultPath . '/' . $resultFileName);

        // //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ
        $link = asset('storage/clients/' . $data['domain'] . '/documents/' . $data['userId'] . '/' . $resultFileName);

        return APIController::getSuccess([
            'data' => $data,
            'link' => $link,
            // 'testInfoblocks' => $testInfoblocks
        ]);
    }

    protected function getInfoblocks($infoblocksOptions, $complect, $section, $sectionStyle)
    {
        $headingStyle = $sectionStyle['heading'];
        $textStyle = $sectionStyle['text'];
        $textStyleBold = $sectionStyle['textBold'];
        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];

        foreach ($complect as $group) {
            $section->addTextBreak(1);
            $section->addText($group['groupsName'], $headingStyle);
            $section->addTextBreak(1);
            foreach ($group['value'] as $infoblock) {
                $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();
                if ($currentInfoblock) {
                    $section->addText($currentInfoblock['name'], $textStyleBold);
                    if ($descriptionMode === 0) {
                    } else   if ($descriptionMode === 1) {
                        $section->addText($currentInfoblock['shortDescription'], $textStyle);
                    } else   if ($descriptionMode === 2) {
                        $section->addText($currentInfoblock['descriptionForSale'], $textStyle);
                    } else   if ($descriptionMode === 3) {
                        $section->addText($currentInfoblock['description'], $textStyle);
                    }

                    $section->addTextBreak(1);
                }
            }
        }


        return $section;
    }
}
