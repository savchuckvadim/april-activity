<?php

namespace App\Http\Controllers;

use App\Models\Infoblock;
use Ramsey\Uuid\Uuid;
use PhpOffice\PhpWord\Shared\Converter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentController extends Controller
{
    public function getDocument($data)

    {
        try {
            $infoblocksOptions = [
                'description' => $data['infoblocks']['description']['current'],
                'style' => $data['infoblocks']['style']['current']['code'],
            ];

            $complect = $data['complect'];

            $templateType = $data['template']['type'];


            //result document
            $resultPath = storage_path('app/public/clients/' . $data['domain'] . '/documents/' . $data['userId']);


            if (!file_exists($resultPath)) {
                mkdir($resultPath, 0775, true); // Создать каталог с правами доступа
            }

            // Проверить доступность каталога для записи
            if (!is_writable($resultPath)) {
                throw new \Exception("Невозможно записать в каталог: $resultPath");
            }



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
                'textSmall' => ['size' => 10, 'name' => 'Arial'],
                'textSmallBold' => ['size' => 10, 'name' => 'Arial', 'bold' => true],
                'textBold' => ['size' => 12, 'name' => 'Arial', 'bold' => true],

            );

            // Создаем стиль абзаца
            $paragraphStyle = array(
                'spaceAfter' => 0,    // Интервал после абзаца
                'spaceBefore' => 0,   // Интервал перед абзацем
                'lineHeight' => 1.15,  // Высота строки
                // Другие параметры стиля абзаца...
            );
            // $languageEnGbStyle = array('lang' => 'ru-RU');

            $section = $phpWord->addSection($sectionStyle);
            $this->getInfoblocks($infoblocksOptions, $complect, $section, $paragraphStyle);

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
        } catch (\Throwable $th) {
            return APIController::getError(
                'something wrong ' . $th->getMessage(),
                [
                    'data' => $data,
                    'styleMode' => $infoblocksOptions['style']

                ]
            );
        }
    }

    protected function getInfoblocks($infoblocksOptions, $complect, $section, $paragraphStyle)
    {

        $totalCount = $this->getInfoblocksCount($complect);
        $headingStyle = ['bold' => true, 'size' => 16, 'name' => 'Arial'];
        $textStyle = ['size' => 12, 'name' => 'Arial'];
        $textStyleBold = ['size' => 12, 'name' => 'Arial', 'bold' => true];
        $textStyleSmall = ['size' => 10, 'name' => 'Arial'];
        $textStyleSmallBold = ['size' => 10, 'name' => 'Arial', 'bold' => true];

        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];


        if ($styleMode == 'list') {
            foreach ($complect as $group) {

                // $section->addText($group['groupsName'], $headingStyle);
                // $section->addTextBreak(1);
                foreach ($group['value'] as $infoblock) {

                    if (array_key_exists('code', $infoblock)) {
                        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();

                        if ($currentInfoblock) {

                            if ($descriptionMode === 0) {
                                $section->addText($currentInfoblock['name'], $textStyleSmall, $paragraphStyle);
                            } else   if ($descriptionMode === 1) {
                                $section->addText($currentInfoblock['name'], $textStyleSmallBold, $paragraphStyle);
                                $section->addText($currentInfoblock['shortDescription'], $textStyleSmall, $paragraphStyle);
                            } else   if ($descriptionMode === 2) {
                                $section->addText($currentInfoblock['name'], $textStyleSmallBold, $paragraphStyle);
                                $section->addText($currentInfoblock['descriptionForSale'], $textStyleSmall, $paragraphStyle);
                            } else   if ($descriptionMode === 3) {
                                $section->addText($currentInfoblock['name'], $textStyleSmall, $paragraphStyle);
                            }
                        }
                    }
                }
            }
        } else if ($styleMode == 'table') {
       
    


            $fullWidth = $section->getStyle()->getPageSizeW();
            $marginRight = $section->getStyle()->getMarginRight();
            $marginLeft = $section->getStyle()->getMarginLeft();
            $contentWidth = ($fullWidth - $marginLeft - $marginRight - 100) / 2;
            $fancyTableStyleName = 'TableStyle';
            $fancyTableStyle = ['borderSize' => 10, 'borderColor' => '000000', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER];
            // 
            $fancyTableFirstRowStyle = ['cellMargin' => 190, 'borderBottomSize' => 18, 'borderBottomColor' => '0000FF', 'bgColor' => '66BBFF']; //,
            // $fancyTableCellStyle = ['valign' => 'center'];
            // $fancyTableCellBtlrStyle = ['valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR];
            $fancyTableFontStyle = ['bold' => true,];

            $fancyTableCellStyle = [
                'valign' => 'center',
                'borderSize' => 6,
                // 'borderColor' => '000000',  // Цвет границы (чёрный)
                'cellMarginTop' => 100,
                'cellMarginRight' => 100,
                'cellMarginBottom' => 100,
                'cellMarginLeft' => 100,
            ];

            $section->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
            $table = $section->addTable($fancyTableStyleName);
            $table->addRow();
            $cell = $table->addCell($contentWidth, $fancyTableCellStyle);
            $count = 0;
            $isTwoColExist = false;
            foreach ($complect as $group) {
                // $table->addCell($contentWidth, $fancyTableCellStyle)->addText($group['groupsName'], $headingStyle);


                foreach ($group['value'] as $infoblock) {

                    if (array_key_exists('code', $infoblock)) {
                        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();

                        if ($currentInfoblock) {


                            if ($count < ($totalCount['infoblocks'] / 2)) {

                                $this->addInfoblockToCell($cell, $currentInfoblock, $descriptionMode, $paragraphStyle);
                            } else {
                                // Если count нечетный, добавляем вторую ячейку в текущую строку
                                if (!$isTwoColExist) {
                                    $cell = $table->addCell($contentWidth, $fancyTableCellStyle);
                                    $isTwoColExist = true;
                                }

                                $this->addInfoblockToCell($cell, $currentInfoblock, $descriptionMode, $paragraphStyle);
                            }
                            // $section->addTextBreak(1);
                            $count = $count  + 1;
                        }
                    }
                }
            }
        } else if ($styleMode == 'tableWithGroup') {
        }


        if ($descriptionMode === 3) {
            $section = $this->getDescriptionPage(
                $complect,
                $section,
                $headingStyle,
                $textStyle,
                $textStyleBold
            );
        }



        return $section;
    }

    protected function getDescriptionPage(
        $complect,
        $section,
        $headingStyle,
        $textStyle,
        $textStyleBold
    ) {
        $section->addPageBreak();
        foreach ($complect as $group) {
            // $section->addTextBreak(1);
            $section->addText($group['groupsName'], $headingStyle);
            $section->addTextBreak(1);
            foreach ($group['value'] as $infoblock) {

                if (array_key_exists('code', $infoblock)) {
                    $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();




                    if ($currentInfoblock) {
                        $section->addText($currentInfoblock['name'], $textStyleBold);

                        $section->addText($currentInfoblock['description'], $textStyle);


                        $section->addTextBreak(1);
                    }
                }
            }
        }
        return $section;
    }

    protected function getInfoblocksCount($complect)
    {
        $result = [
            'groups' => 0,
            'infoblocks' => 0
        ];


        foreach ($complect as $group) {
            $result['groups'] += 1;

            foreach ($group['value'] as $infoblock) {
                $result['infoblocks'] += 1;
            }
        }
        return  $result;
    }

    protected function addInfoblockToCell($cell, $infoblock, $descriptionMode, $paragraphStyle)
    {
        $headingStyle = ['bold' => true, 'size' => 16, 'name' => 'Arial'];
        $textStyle = ['size' => 12, 'name' => 'Arial'];
        $textStyleBold = ['size' => 12, 'name' => 'Arial', 'bold' => true];
        $textStyleSmall = ['size' => 10, 'name' => 'Arial'];
        $textStyleSmallBold = ['size' => 10, 'name' => 'Arial', 'bold' => true];
        switch ($descriptionMode) {
            case 0:
                $cell->addText($infoblock['name'], $textStyleSmall, $paragraphStyle);
                break;
            case 1:
                $cell->addText($infoblock['name'], $textStyleSmallBold, $paragraphStyle);
                $cell->addText($infoblock['shortDescription'], $textStyleSmall, $paragraphStyle);
                break;
            case 2:
            case 3:
                $cell->addText($infoblock['name'], $textStyleSmallBold, $paragraphStyle);
                $cell->addText($infoblock['descriptionForSale'], $textStyleSmall, $paragraphStyle);
                break;
        }
    }
}
