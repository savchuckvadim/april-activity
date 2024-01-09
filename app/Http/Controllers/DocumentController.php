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
        } catch (\Throwable $th) {
            return APIController::getError(
                $th->getMessage(),
                [
                    'data' => $data,
                    'styleMode' => $infoblocksOptions['style']

                ]
            );
        }
    }

    protected function getInfoblocks($infoblocksOptions, $complect, $section, $sectionStyle)
    {
        $headingStyle = $sectionStyle['heading'];
        $textStyle = $sectionStyle['text'];
        $textStyleBold = $sectionStyle['textBold'];
        $descriptionMode = $infoblocksOptions['description']['id'];
        $styleMode = $infoblocksOptions['style'];


        if ($styleMode == 'list') {
            foreach ($complect as $group) {
                $section->addTextBreak(1);
                $section->addText($group['groupsName'], $headingStyle);
                $section->addTextBreak(1);
                foreach ($group['value'] as $infoblock) {

                    if (array_key_exists('code', $infoblock)) {
                        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();




                        if ($currentInfoblock) {
                            $section->addText($currentInfoblock['name'], $textStyleBold);
                            if ($descriptionMode === 0) {
                            } else   if ($descriptionMode === 1) {
                                $section->addText($currentInfoblock['shortDescription'], $textStyle);
                            } else   if ($descriptionMode === 2) {
                                $section->addText($currentInfoblock['descriptionForSale'], $textStyle);
                            } else   if ($descriptionMode === 3) {
                                $section->addText($currentInfoblock['descriptionForSale'], $textStyle);
                            }

                            $section->addTextBreak(1);
                        }
                    }
                }
            }
        } else if ($styleMode == 'table') {
            $fancyTableStyleName = 'Информационное наполнение';
            $fancyTableStyle = ['borderSize' => 0, 'borderColor' => 'FFFFF', 'cellMargin' => 25, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER];
            $fancyTableFirstRowStyle = ['cellMargin' => 25,]; //'borderBottomSize' => 18, 'borderBottomColor' => '0000FF', 'bgColor' => '66BBFF',
            $fancyTableCellStyle = ['valign' => 'center'];
            // $fancyTableCellBtlrStyle = ['valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR];
            $fancyTableFontStyle = ['bold' => true,];


            $table = $section->addTable($fancyTableStyleName);
            $table->addRow(90);
            $sectionStyle = $section->getStyle();
            $fullWidth = $sectionStyle->getPageSizeW();
            $marginRight = $sectionStyle->getMarginRight();
            $marginLeft = $sectionStyle->getMarginLeft();
            $contentWidth = $fullWidth - $marginLeft - $marginRight;

            foreach ($complect as $group) {
                // $table->addCell($contentWidth, $fancyTableCellStyle)->addText($group['groupsName'], $headingStyle);
                $count = 0;
                foreach ($group['value'] as $index => $infoblock) {
                    if (array_key_exists('code', $infoblock)) {
                        $currentInfoblock = Infoblock::where('code', $infoblock['code'])->first();

                        if ($currentInfoblock) {



                            if ($descriptionMode === 0) {
                                $table->addRow(90);
                                $cell = $table->addCell($contentWidth, $fancyTableCellStyle);

                                $cell->addText($currentInfoblock['name'], $textStyleBold);
                            } else   if ($descriptionMode === 1) {

                                if ($count % 2 == 0) { // Для четных индексов начинаем новую строку
                                    $table->addRow(90);
                                    $leftCell = $table->addCell($contentWidth, $fancyTableCellStyle);
                                    $leftCell->addText($currentInfoblock['name'], $headingStyle);
                                    $leftCell->addText($currentInfoblock['shortDescription'], $textStyle);
                                } else { // Для нечетных индексов добавляем ячейку в ту же строку
                                    $rightCell = $table->addCell($contentWidth, $fancyTableCellStyle);
                                    $rightCell->addText($currentInfoblock['name'], $headingStyle);
                                    $rightCell->addText($currentInfoblock['shortDescription'], $textStyle);
                                }


                                // $table->addRow(90);
                                // $cell =  $table->addCell($contentWidth, $fancyTableCellStyle);
                                // $cell->addText($currentInfoblock['name'], $headingStyle);
                                // $cell->addText($currentInfoblock['shortDescription'], $textStyle);
                            } else   if ($descriptionMode === 2) {
                                if ($count % 2 == 0) { // Для четных индексов начинаем новую строку
                                    $table->addRow(90);
                                    $leftCell = $table->addCell($contentWidth, $fancyTableCellStyle);
                                    $leftCell->addText($currentInfoblock['name'], $headingStyle);
                                    $leftCell->addText($currentInfoblock['descriptionForSale'], $textStyle);
                                } else { // Для нечетных индексов добавляем ячейку в ту же строку
                                    $rightCell = $table->addCell($contentWidth, $fancyTableCellStyle);
                                    $rightCell->addText($currentInfoblock['name'], $headingStyle);
                                    $rightCell->addText($currentInfoblock['descriptionForSale'], $textStyle);
                                }
                            } else   if ($descriptionMode === 3) {
                                if ($count % 2 == 0) { // Для четных индексов начинаем новую строку
                                    $table->addRow(90);
                                    $leftCell = $table->addCell($contentWidth, $fancyTableCellStyle);
                                    $leftCell->addText($currentInfoblock['name'], $headingStyle);
                                    $leftCell->addText($currentInfoblock['descriptionForSale'], $textStyle);
                                } else { // Для нечетных индексов добавляем ячейку в ту же строку
                                    $rightCell = $table->addCell($contentWidth, $fancyTableCellStyle);
                                    $rightCell->addText($currentInfoblock['name'], $headingStyle);
                                    $rightCell->addText($currentInfoblock['descriptionForSale'], $textStyle);
                                }
                            }

                            $section->addTextBreak(1);
                            $count++;
                        }
                    }
                }
            }
        } else if ($styleMode == 'tableWithGroup') {
        }



        return $section;
    }
}
