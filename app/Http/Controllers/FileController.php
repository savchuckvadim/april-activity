<?php

namespace App\Http\Controllers;

use App\Http\Resources\RqResource;
use App\Models\File;
use App\Models\Rq;
use App\Models\Template;
use CRest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\TemplateProcessor;
use Ramsey\Uuid\Uuid;

const MON = 'мес.';
const ABON_6 = 'абон. 6 мес.';
const ABON_12 = 'абон. 12 мес.';
const ABON_24 = 'абон. 24 мес.';
const LIC_6 = 'лиц. 6 мес.';
const LIC_12 = 'лиц. 12 мес.';
const LIC_24 = 'лиц. 24 мес.';


class FileController extends Controller
{
    public static function processFields(Request $request)
    {
        $fetchedfields = $request->input('aprilfields');
        $aprilFields = [];
        foreach ($fetchedfields as $field) {
            $newField = [
                'name' => $field['name'],
                'bitrixId' => $field['bitrixId']
            ];
            array_push($aprilFields, $newField);
        };

        // Проверяем наличие поля fields в запросе
        if (!$request->has('aprilfields')) {
            return response()->json(['status' => 'error', 'message' => 'Поле fields отсутствует'], 200);
        }



        // Проверяем, является ли $fields массивом
        if (!is_array($aprilFields)) {
            return response()->json(['status' => 'error', 'message' => 'Поле fields должно быть массивом'], 200);
        }

        // Путь к исходному и результирующему файлам
        $filename = $request->fileName;
        // $filename = $file->getClientOriginalName();
        // Storage::disk('public')->put($filename, 'test');

        $templatePath = 'C:\Projects\April-KP\placements\storage\app\public\\' . $filename;
        $resultPath = storage_path('app/public/' . $filename);

        // Проверяем, существует ли исходный файл
        if (!file_exists($templatePath)) {
            return response()->json(['status' => 'error', 'message' => 'Исходный файл не найден', 'templatePath' => $templatePath], 200);
        }

        try {
            $template = new TemplateProcessor($templatePath);
            //
            // Заменяем каждое вхождение field->name на field->bitrixId
            foreach ($aprilFields as $field) {
                if (!isset($field['name']) || !isset($field['bitrixId'])) {
                    return response(['status' => 'error', 'fields' => $aprilFields], 200);
                }

                $template->setValue($field['name'], $field['bitrixId']);
            }

            // Сохраняем результат
            $template->saveAs($resultPath);
            $link = asset('storage/' . $filename);
        } catch (Exception $e) {
            // Обрабатываем возможные исключения
            return response()->json(['status' => 'error', 'message' => 'Ошибка обработки шаблона: ' . $e->getMessage()], 200);
        }

        // Возвращаем успешный ответ
        return response([
            'resultCode' => 0,
            'status' => 'success',
            'message' => 'Обработка прошла успешно',
            'templatePath' =>  $templatePath,
            'file' =>  $link,
            'fields' => $aprilFields

        ]);
    }



    public static function getInitial()
    {

        $initialData = File::getForm();
        $data = [
            'initial' => $initialData
        ];
        return APIController::getSuccess($data);
    }




    public static function getFile(Request $request)
    {
        $base64 = base64_encode(file_get_contents('/path/to/your/file.docx')); // Замените '/path/to/your/file.docx' на реальный путь до вашего файла
        $DEAL_ID = 1; // Используйте реальный ID вашей сделки

        CRest::call(
            'crm.deal.update',
            [
                'id' => $DEAL_ID,
                'fields' => [
                    "UF_CRM_1234567890" => [ // Замените "UF_CRM_1234567890" на код вашего пользовательского поля
                        'fileData' => ["file.docx", $base64] // Используйте реальное имя вашего файла
                    ],
                ],
                'params' => [
                    "REGISTER_SONET_EVENT" => "Y"
                ]
            ]
        );
    }

    public static function uploadDescriptionTemplate(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = 'Description';
            // time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('description/general', $filename, 'public');

            return response()->json(['message' => 'File uploaded successfully']);
        }
        return response()->json(['message' => 'No file uploaded']);
    }

    public function setFile($entityType, $parentType, $parentId, Request $request)
    {
        //entityType logo | stamp | последняя или единственная часть урл
        // $parentType - может быть null тогда можно взять из formdata
        // parentId - id родительского элемента
        $fieldData = [
            'name' => $request['name'],
            'type' => $request['type'], //Тип файла (table | video | word | img)
            'parent' => $request['parent'], //Родительская модель (rq)
            'parent_type' => $request['parent_type'], //Название файла в родительской модели logo stamp
            'availability' => $request['availability'], //Доступность public |  local
            '$entityType' => $entityType,
            '$parentType' => $parentType,
            '$parentId' => $parentId,
            'file' => $request['file'],

        ];


        if ($parentType) {
            switch ($parentType) {
                case 'rq':
                    switch ($entityType) {
                        case 'logo':
                            if ($request->hasFile('file_0')) {
                                $file = $request->file('file_0');

                                // Проверяем, является ли файл экземпляром UploadedFile и был ли он успешно загружен
                                if ($file instanceof UploadedFile && $file->isValid()) {
                                    // Обрабатываем файл, например, сохраняем его
                                    // $filePath = $file->store('path/to/store', 'disk_name');

                                    // Сохраняем путь к файлу в $fieldData
                                    // $fieldData['file'] = $file;
                                    $fieldData = [
                                        'name' => $request['name'],
                                        'type' => $request['type'], //Тип файла (table | video | word | img)
                                        'parent' => $request['parent'], //Родительская модель (rq)
                                        'parent_type' => $request['parent_type'], //Название файла в родительской модели logo stamp
                                        'availability' => $request['availability'], //Доступность public |  local

    

                                    ];
                                    $parent = Rq::find($parentId);

                                    $domain = $parent->agent->portal['domain'];
                                    $fileModel = new File();
                                    $fileModel->name = $fieldData['name'];
                                    $fileModel->type = $fieldData['type'];
                                    $fileModel->parent = $fieldData['parent'];
                                    $fileModel->parent_type = $fieldData['parent_type'];
                                    $fileModel->availability = $fieldData['availability'];


                                    $uid = Uuid::uuid4()->toString();
                                    $code = $uid;
                                    $fileModel->code = $code;

                                   
                                    // $fileModel->parent = $fieldData['parent'];

                                    $generalDirectoryPath = 'clients/' . $domain;
                                    $filePath = $fieldData['parent'] . '/' . $parentId . '/' . $fieldData['parent_type'];
                                    $fileName = $parentId . '_' . $fieldData['parent_type'] . '_' . $code;

                                    $uploadData = $this->uploadFile(
                                        $file,
                                        $fileName,
                                        $fieldData['availability'],
                                        $generalDirectoryPath,
                                        $filePath

                                    );
                                    if ($uploadData && $uploadData['resultCode'] == 0 && $uploadData['filePath']) {
                                        $fileModel->path = $uploadData['filePath'];
                                        $fileModel->entity()->associate($parent); // Устанавливаем связь с родительской моделью
                                        $fileModel->save();
                                        return APIController::getSuccess([
                                            $entityType => $fileModel
                                        ]);
                                    }
                                    return APIController::getError(
                                        'file not saved',
                                        ['uploadData' => $uploadData]
                                    );
                                }
                                return APIController::getError(
                                    'invalid data file',
                                    ['file' => $file]
                                );
                            }

                        case 'signature':
                        case 'stamp':
                        case 'qr':
                        case 'file':
                    }

                case 'template':
                case 'field':
                case 'qr':
                case 'file':
            }
        }
    }

    protected function uploadFile(
        $file,
        $filename,
        $availability, //public | other non public directory
        $direct,        // clients | documents | rqs
        $filePath,

    ) {

        if ($availability === 'public') {
            //save PUBLIC
            // /storage/app/public/clients
            //         При использовании диска public, вы указываете относительный 
            // путь от storage/app/public. Например, 
            // если вы хотите сохранить файл в storage/app/public/clients, то в методе storeAs вы укажете только 'clients/'.
            // Пример: $file->storeAs('clients/', $filename, 'public');


            $relativePath = $direct . '/' . $filePath;
            $file->storeAs($relativePath, $filename, 'public');
            $fullPath = 'storage/' . $relativePath . '/' . $filename;

            return [
                'resultCode' => 0,
                'filePath' => $fullPath,
            ];
        } else {
            //save LOCAL
            // Диск local используется для хранения файлов внутри storage/app, которые не должны быть доступны напрямую через веб.
            // Когда вы используете диск local, вам не нужно указывать app/ в пути, так как это уже предполагается по умолчанию.
            // Пример: $file->storeAs($resultPath, $filename, 'local');, где $resultPath - это путь внутри storage/app.
            $relativePath = $direct  .  '/' . $filePath  . '/' . $filename;
            $file->storeAs($relativePath, $filename, 'local');

            return [
                'resultCode' => 0,
                'filePath' => $relativePath,

            ];
        }

        return [
            'resultCode' => 1,
            'filePath' => null,
        ];
    }
    protected function getFilePath(
        $filename,
        $availability, //public | other non public directory
        $direct,        // clients | documents | rqs
        $domain,
        $filePath,

    ) {
        $relativePath = $direct . '/' . $domain . '/' . $filePath . '/' . $filename;

        if ($availability === 'public') {
            // Для публичных файлов
            $url = asset('storage/' . ltrim($relativePath, '/')); // Удаление начального слеша, если он есть
            return [
                'resultCode' => 0,
                'filePath' => $relativePath,
                'url' => $url
            ];
        } else {
            // Для локальных файлов
            $absolutePath = storage_path('app/' . ltrim($relativePath, '/')); // Удаление начального слеша, если он есть
            return [
                'resultCode' => 0,
                'filePath' => $relativePath,
                'absolutePath' => $absolutePath
            ];
        }

        return [
            'resultCode' => 1,
            'filePath' => null,
            'url' => null
        ];
    }

    public function updateFile(Request $request, $fileId)
    {
        // $newFile = $request->file('file'); // Новый файл из запроса
        // $fileRecord = FileModel::find($fileId); // Находим старый файл в БД по ID

        // if ($fileRecord && $newFile) {
        //     // Удаляем старый файл
        //     Storage::delete($fileRecord->path);

        //     // Сохраняем новый файл
        //     $relativePath = 'your/path/here'; // Определите путь для сохранения нового файла
        //     $newFilename = $newFile->getClientOriginalName();
        //     $newFile->storeAs($relativePath, $newFilename, 'public'); // Или 'local', в зависимости от диска

        //     // Обновляем запись в БД
        //     $fileRecord->path = $relativePath . '/' . $newFilename;
        //     $fileRecord->save();

        //     return response()->json(['message' => 'File updated successfully']);
        // }

        // return response()->json(['message' => 'File not found or no new file provided'], 404);
    }


    public static function getGeneralDescription(Request $request)
    {

        $domain = $request->input('domain');
        $userId = $request->input('userId');
        $complect = $request->input('complect');
        $complectName = $complect['complectName'];
        $supply = $complect['supply'];

        $infoblocks = $request->input('infoblocks');

        $groups = $request->input('groups');
        // [
        //     ['groupName' => 1, 'groupName' => 'Нормативно-Правовые акты'],
        //     ['groupName' => 2, 'groupName' => 'Судебная практика']

        // ];

        // Путь к исходному и результирующему файлам
        $resultFileName = 'Описание_Комплекта_Гарант_' . $userId . '.docx';


        $templatePath = storage_path() . '/app/public/description/general/Description';
        $resultPath = storage_path('app/public/description/' . $domain);

        // Проверяем, существует ли исходный файл
        if (!file_exists($resultPath)) {
            mkdir($resultPath, 0777, true); // Создаем директорию с правами 0777 рекурсивно
        }

        try {
            $template = new TemplateProcessor($templatePath);

            // $template->cloneRowAndSetValues('groupId', $groups);
            $template->setValue('complectName', $complectName);
            $template->setValue('supply', $supply);


            ////С НОВОЙ СТРОКИ
            foreach ($infoblocks as &$infoblock) {
                if (isset($infoblock['description'])) {
                    $infoblock['description'] = str_replace("\n", "</w:t><w:br/><w:t>", $infoblock['description']);
                }
            }
            unset($infoblock);  // разъединяем ссылку на последний элемент
            //////////////////

            $template->cloneRowAndSetValues('infoblockId', $infoblocks);
            // $template->cloneRowAndSetValues('name', $complect->infoblocks);
            // Сохраняем результат
            $template->saveAs($resultPath . '/' . $resultFileName);
            $link = asset('storage/description/' . $domain . '/' . $resultFileName);
            // Читаем файл и кодируем его содержимое в Base64
            $fileContent = file_get_contents($resultPath . '/' . $resultFileName);
            $base64File = base64_encode($fileContent);
        } catch (Exception $e) {
            // Обрабатываем возможные исключения
            return response()->json(['status' => 'error', 'message' => 'Ошибка обработки шаблона: ' . $e->getMessage()], 200);
        }

        // Возвращаем успешный ответ
        return response([
            'resultCode' => 0,
            'status' => 'success',
            'message' => 'Обработка прошла успешно',
            'templatePath' =>  $templatePath,
            'file' =>  $link,
            'fileBase64' => $base64File,

        ]);
    }




    public static function getGeneralOffer(Request $request)
    {

        $domain = $request->input('domain');
        $userId = $request->input('userId');
        $complect = $request->input('complect');
        $complectName = $complect['complectName'];
        $supply = $complect['supply'];

        $infoblocks = $request->input('infoblocks');

        $groups = $request->input('groups');
        // [
        //     ['groupName' => 1, 'groupName' => 'Нормативно-Правовые акты'],
        //     ['groupName' => 2, 'groupName' => 'Судебная практика']

        // ];

        // Путь к исходному и результирующему файлам
        $resultFileName = 'Коммерческое предложение_Гарант_' . $userId . '.docx';


        // $templatePath = storage_path() . '/app/public/description/general/Description';
        // $resultPath = storage_path('app/public/description/' . $domain);

        // // Проверяем, существует ли исходный файл
        // if (!file_exists($resultPath)) {
        //     mkdir($resultPath, 0777, true); // Создаем директорию с правами 0777 рекурсивно
        // }

        try {
            $phpWord = new PhpWord();
            $section = $phpWord->addSection();



            $sectionTitleStyle = array(
                'bold' => true,
            );

            $itemNameStyle = array();







            // $template = new TemplateProcessor($templatePath);
            // $template->setValue('complectName', $complectName);
            // $template->setValue('supply', $supply);


            ////С НОВОЙ СТРОКИ
            foreach ($infoblocks as &$infoblock) {
                if (isset($infoblock['description'])) {
                    $infoblock['description'] = str_replace("\n", "</w:t><w:br/><w:t>", $infoblock['description']);
                }
            }
            unset($infoblock);  // разъединяем ссылку на последний элемент
            //////////////////

            // $template->cloneRowAndSetValues('infoblockId', $infoblocks);
            // // Сохраняем результат
            // $template->saveAs($resultPath . '/' . $resultFileName);
            // $link = asset('storage/description/' . $domain . '/' . $resultFileName);


            // // Читаем файл и кодируем его содержимое в Base64
            // $fileContent = file_get_contents($resultPath . '/' . $resultFileName);
            // $base64File = base64_encode($fileContent);



        } catch (Exception $e) {
            // Обрабатываем возможные исключения
            return response()->json(['status' => 'error', 'message' => 'Ошибка обработки шаблона: ' . $e->getMessage()], 200);
        }

        // Возвращаем успешный ответ
        return response([
            'resultCode' => 0,
            'status' => 'success',
            'infoblocks' => $infoblocks
            // 'message' => 'Обработка прошла успешно',
            // 'templatePath' =>  $templatePath,
            // 'file' =>  $link,
            // 'fileBase64' => $base64File,

        ]);
    }



    public static function upload(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads', $filename, 'public');

            return response()->json(['message' => 'File uploaded successfully']);
        }
        return response()->json(['message' => 'No file uploaded']);
    }


    public static function uploadPortalTemplate(Request $request)
    {
        // formData.append('file', file);
        // formData.append('portal', props.portal);
        // formData.append('type', props.type);
        // formData.append('fileName', props.fileName);



        if ($request->hasFile('file') && $request->has('portal') && $request->has('type') && $request->has('fileName')) {
            $file = $request->file('file');
            $filename = $request->input('fileName');
            $filename =  $filename . 'docx';
            $portal = $request->input('portal');
            $type = $request->input('type');
            $file->storeAs('clients/' . $portal . '/templates/' . $type, $filename, 'public');

            return response()->json([
                'message' => 'File uploaded successfully',
                'filename' =>  $filename,
                'portal' =>  $portal,
                'file' =>  $file,
                'type' =>  $type,

            ]);
        }
        return response()->json(['message' => 'No file uploaded']);
    }



    public static function uploadTemplate($file, $domain, $type, $name)
    {
        // formData.append('file', file);
        // formData.append('portal', props.portal);
        // formData.append('type', props.type);
        // formData.append('fileName', props.fileName);



        if ($file && $domain && $type && $name) {

            $filename =  $name . '.docx';
            $file->storeAs('clients/' . $domain . '/templates/' . $type, $filename, 'public');
            // $url = Storage::disk('public')->url('clients/' . $domain . '/templates/' . $type . '/' . $filename);
            // $link = asset('storage/clients/' . $domain . '/templates' . $type);
            $absolutePath = storage_path('app/public/clients/' . $domain . '/templates/' . $type . '/' . $filename);

            return [
                'result' => 0,
                'message' => 'File uploaded successfully',
                'filename' =>  $filename,
                'file' =>  $absolutePath,

            ];
        }
        return [
            'result' => 1,
        ];
    }

    public static function getDocument(
        $templateData,
        $domain,
        $userId,
        $price,
        $infoblocks,
        $provider,
        $recipient


    ) {

        // $domain = $request->input('domain');
        // $userId = $request->input('userId');
        // $complect = $request->input('complect');
        // $complectName = $complect['complectName'];
        // $supply = $complect['supply'];
        // $templateId = $request['templateId'];                                   
        // $infoblocks = $request->input('infoblocks');

        // $groups = $request->input('groups');
        // [
        //     ['groupName' => 1, 'groupName' => 'Нормативно-Правовые акты'],
        //     ['groupName' => 2, 'groupName' => 'Судебная практика']

        // ];

        // Путь к исходному и результирующему файлам
        $uid = Uuid::uuid4()->toString();
        $resultFileName = $templateData['type'] . '_' . $uid . '.docx';


        // $templatePath = Template::getTemplatePath($templateData['id']);
        // storage_path() . '/app/public/description/general/Description';
        $resultPath = storage_path('app/public/clients/' . $domain . '/documents/' . $userId);

        // Проверяем, существует ли исходный файл
        if (!file_exists($resultPath)) {
            mkdir($resultPath, 0777, true); // Создаем директорию с правами 0777 рекурсивно
        }






        try {
            // $template = new TemplateProcessor($templatePath);

            // $template->cloneRowAndSetValues('groupId', $groups);
            // $template->setValue('complectName', $complectName);
            // $template->setValue('supply', $supply);


            // ////С НОВОЙ СТРОКИ
            // foreach ($infoblocks as $infoblock) {
            //     if (isset($infoblock['description'])) {
            //         $infoblock['description'] = str_replace("\n", "</w:t><w:br/><w:t>", $infoblock['description']);
            //     }
            // }
            // unset($infoblock);  // разъединяем ссылку на последний элемент
            // //////////////////

            // $template->cloneRowAndSetValues('infoblockId', $infoblocks);
            // $template->cloneRowAndSetValues('name', $complect->infoblocks);
            // Сохраняем результат
            // $template->saveAs($resultPath . '/' . $resultFileName);
            // $link = asset('storage/description/' . $domain . '/' . $resultFileName);
            // // Читаем файл и кодируем его содержимое в Base64
            // $fileContent = file_get_contents($resultPath . '/' . $resultFileName);
            // $base64File = base64_encode($fileContent);


            /////////////////////CREATE WORD

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

            $priceDataGeneral = $price['cells']['general'][0]['cells'];
            $priceDataAlternative = $price['cells']['alternative'][0]['cells'];
            $priceDataTotal = $price['cells']['total'][0]['cells'];
            $cells = [];
            $isTable = $price['isTable'];

            //HTML
            // $html = '<table style="border-collapse: collapse;">';
            // foreach ($priceData as $row) {
            //     if ($row['isActive']) {
            //         $html .= '<tr>';
            //         // Добавьте ячейки для каждого активного элемента
            //         // Например: $html .= '<td>' . htmlspecialchars($row->name) . '</td>';
            //         $html .= '<td style="border: 1px solid black;">' . htmlspecialchars($row['value']) . '</td>';
            //         $html .= '</tr>';
            //     }
            // }

            // $html .= '</table>';
            // \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html);
            $header = $section->addHeader();
            //ПОСТАВЩИК
            $providerName =  $provider['rq']['fullname'];
            $providerEmail =  $provider['rq']['email'];
            $providerPhone =  $provider['rq']['phone'];
            $providerInn =  $provider['rq']['inn'];
            $rowsProviderRqtable = [];

            if (!empty(trim($providerName))) {
                array_push($rowsProviderRqtable, $providerName);
            }
            if (!empty(trim($providerInn))) {
                array_push($rowsProviderRqtable, 'ИНН: ' . $providerInn);
            }
            if (!empty(trim($providerEmail))) {
                array_push($rowsProviderRqtable, $providerEmail);
            }
            if (!empty(trim($providerPhone))) {
                array_push($rowsProviderRqtable, $providerPhone);
            }



            // Стиль для выравнивания текста
            $leftAlignedStyle = array(
                'align' => 'left',
                'spaceAfter' => 0,
                'spaceBefore' => 0,
                [...$languageEnGbStyle]

            );
            $providerRqfontStyle = array('size' => 9);
            // Определение ширины страницы и расчет ширины ячейки
            $pageWidth = $section->getStyle()->getPageSizeW();
            $cellWidth = $pageWidth / 3;  // Четверть ширины страницы
            $providerRqtable = $header->addTable();

            foreach ($rowsProviderRqtable as $row) {
                $providerRqtable->addRow();
                $providerRqtable->addCell($cellWidth)->addText($row, $providerRqfontStyle, $leftAlignedStyle);
            }






            //ТАБЛИЦА ЦЕН


            // $header = ['size' => 16, 'bold' => true];

            // $headerRqStyle = ['valign' => 'left'];
            // $header->addText($combinedRq, $headerRqStyle);
            // $header->addImage('path_to_logo.jpg', $logoStyle);

            $section->addTextBreak(1);
            $section->addText('Цена за комплект', $languageEnGbStyle);

            $fancyTableStyleName = 'Цена за комплект';

            $sectionStyle = $section->getStyle();
            $fullWidth = $sectionStyle->getPageSizeW();
            $marginRight = $sectionStyle->getMarginRight();
            $marginLeft = $sectionStyle->getMarginLeft();
            $contentWidth = $fullWidth - $marginLeft - $marginRight;


            //TABLE

            usort($price['cells']['general'], function ($a, $b) {
                return $a->order - $b->order;
            });
            usort($price['cells']['alternative'], function ($a, $b) {
                return $a->order - $b->order;
            });
            if ($isTable) {

                // Расчет ширины каждой ячейки в зависимости от количества столбцов
                $activePriceCellsGeneral = array_filter($priceDataGeneral, function ($prc) {
                    return $prc['isActive'];
                });
                $activePriceCellsAlternative = array_filter($priceDataAlternative, function ($prc) {
                    return $prc['isActive'];
                });

                $numCells = count($activePriceCellsGeneral); // Количество столбцов
                $cellWidth = $contentWidth / $numCells;

                $fancyTableStyle = ['borderSize' => 0, 'borderColor' => 'FFFFF', 'cellMargin' => 25, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER];
                $fancyTableFirstRowStyle = ['cellMargin' => 25,]; //'borderBottomSize' => 18, 'borderBottomColor' => '0000FF', 'bgColor' => '66BBFF',
                $fancyTableCellStyle = ['valign' => 'center'];
                // $fancyTableCellBtlrStyle = ['valign' => 'center', 'textDirection' => \PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR];
                $fancyTableFontStyle = [...$languageEnGbStyle, 'bold' => true,];
                $phpWord->addTableStyle($fancyTableStyleName, $fancyTableStyle, $fancyTableFirstRowStyle);
                $table = $section->addTable($fancyTableStyleName);
                $table->addRow(90);

                foreach ($activePriceCellsGeneral as $priceCell) {
                    $table->addCell($cellWidth, $fancyTableCellStyle)->addText($priceCell['name'], $fancyTableFontStyle);
                }
                $table->addRow();
                foreach ($price['cells']['general'] as $prc) {
                    foreach ($prc['cells'] as $cll) {

                        if ($cll['isActive']) {

                            $value = $cll['code']  === "discountprecent" ? round((100 -  $cll['value'] * 100), 2) : $cll['value'];
                            $table->addCell($cellWidth, $fancyTableCellStyle)->addText($value, $fancyTableFontStyle);
                        }
                    }
                }
                $table->addRow();
                foreach ($price['cells']['alternative'] as $prc) {
                    foreach ($prc['cells'] as $cll) {
                        if ($cll['isActive']) {
                            $value = $cll['code']  === "discountprecent" ? round((100 -  $cll['value'] * 100), 2) : $cll['value'];
                            $table->addCell($cellWidth, $fancyTableCellStyle)->addText($value, $fancyTableFontStyle);
                        }
                    }
                }
            } else {

                //NOTABLE PRICE
                $section->addTextBreak(1);

                // Сортировка массива

                $measure = null;
                foreach ($price['cells']['general'] as $prc) {
                    $text = '';
                    foreach ($prc['cells'] as $cll) {
                        if ($cll['code'] === 'measure') {
                            $measure = $cll['value'];
                            break; // Прерываем цикл, как только найдем нужный элемент
                        }
                    }
                }



                $texts = [];
                $values = [];
                $quantity = null;
                $clls = [];
                $allPrices = [];
                $allPrices[] = $price['cells']['general'];
                $allPrices[] = $price['cells']['alternative'];
                // Создание стилей
                $boldStyle = array('bold' => true);
                $colorStyle = array('bold' => true, 'color' => 'FF0000'); // Красный цвет
                $phpWord->addFontStyle('BoldText', array('bold' => true));
                $phpWord->addFontStyle('ColorBoldText', array('bold' => true, 'color' => 'FF0000'));
                if ($measure) {
                    foreach ($allPrices as $target) {

                        foreach ($target as $prc) {
                            $text = '';

                            $textRunBold = $section->addTextRun('ColorBoldText');
                            $isQantityNamed = false;
                            $isQantityPrepaymentNamed = false;

                            $isSumNamed = false;
                            $isSumPrepaymentNamed = false;
                            $isSumContractNamed = false;
                            $string = '';
                            foreach ($prc['cells'] as $cll) {
                                $value = '';
                                $applyStyle = null;
                                // $textRun = $section->addTextRun($languageEnGbStyle);
                                $isNamed = false;

                                if ($cll['isActive'] && !empty(trim($cll['value']))) {

                                    if (
                                        $cll['name'] === 'При заключении договора от' ||
                                        $cll['name'] === 'При внесении предоплаты от'


                                    ) {

                                        $isNamed = $cll;
                                        $clls[] = $cll;
                                        $value = $cll['name'] . ' ';

                                        $texts[] = $value;
                                    } else if (

                                        $cll['name'] === 'Сумма за весь период обслуживания' ||
                                        $cll['name'] === 'Сумма предоплаты' ||
                                        $cll['name'] === 'Сумма'


                                    ) {
                                        $length = strlen($string);
                                        if ($length > 75) {
                                            $textRunBold->addTextBreak();
                                        }

                                        $isNamed = $cll;
                                        $clls[] = $cll;
                                        $value = $cll['name'];
                                        $texts[] = $value;
                                    } else if (

                                        $cll['name'] === 'Цена в месяц' ||
                                        $cll['name'] === 'Цена'

                                    ) {
                                        $isNamed = $cll;
                                        $clls[] = $cll;
                                        $value = $cll['name'];

                                        $texts[] = $value;
                                    } else if ($cll['code'] === 'quantity') {
                                        $value = FileController::getFullMeasureValue($measure, $cll['value']) . '';
                                        $texts[] = $value;
                                        $quantity =  $cll['value'];
                                    } else if ($cll['code']  === "discountprecent") {
                                        $value =  round((100 -  $cll['value'] * 100), 2) . '';
                                        $texts[] = $value;
                                    } else if ($cll['code']  === "measure") {
                                        // $value = $cll['value'] . '';
                                        $texts[] = $value;
                                    } else {

                                        $value = $cll['value'] . '';
                                        $texts[] = $value;
                                    }

                                    if ($isNamed) {
                                        $text = $text . '  ' . $value;
                                        if ((
                                            $cll['name'] === 'Сумма за весь период обслуживания' ||
                                            $cll['name'] === 'Сумма предоплаты' ||
                                            $cll['name'] === 'Сумма'
                                        ) && $length > 75) {
                                            $textRunBold->addText($value, $boldStyle);
                                            $textRunBold->addText('  ' . $isNamed['value'], $colorStyle);
                                        } else {
                                            $textRunBold->addText('  ' . $value, $boldStyle);
                                            $textRunBold->addText('  ' . $isNamed['value'], $colorStyle);
                                        }

                                        $string = $string . '  ' . $value;
                                        $string = $string . '  ' . $isNamed['value'];
                                    } else {
                                        if (empty(trim($text))) {
                                            $text = $value;
                                            $string = $string . $value;
                                            $textRunBold->addText($value,  $boldStyle);
                                        } else {
                                            $text = $text . '  ' . $value;
                                            $string = $string . '  ' . $value;
                                            $textRunBold->addText('  ' . $value, $boldStyle);
                                        }
                                    }





                                    // Добавляем текст с соответствующим стилем
                                    // $text = $cll['name'] . ' ' . $cll['value'] . ' ';

                                }
                                // // Добавляем перенос строки после обработки всех ячеек в строке

                            }
                            // $textRunBold->addText($text);
                            // $section->addText($text);
                            // if ($applyStyle) {
                            //     $section->addText($text, $applyStyle);
                            // } else {
                            //     $section->addText($text, $languageEnGbStyle);
                            // }
                        }
                    }
                }
            }








            //СОХРАНЕНИЕ ДОКУМЕТА
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($resultPath . '/' . $resultFileName);

            //ГЕНЕРАЦИЯ ССЫЛКИ НА ДОКУМЕНТ
            $link = asset('storage/clients/' . $domain . '/documents/' . $userId . '/' . $resultFileName);



            // Читаем файл и кодируем его содержимое в Base64
            $fileContent = file_get_contents($resultPath . '/' . $resultFileName);
            $base64File = base64_encode($fileContent);
        } catch (Exception $e) {
            // Обрабатываем возможные исключения
            return response()->json(['status' => 'error', 'message' => 'Ошибка обработки шаблона: ' . $e->getMessage()], 200);
        }

        // Возвращаем успешный ответ
        return response([
            'resultCode' => 0,
            'status' => 'success',
            'message' => 'Обработка прошла успешно',
            // 'templatePath' =>  $templatePath,
            '$resultPath' => $resultPath,
            'template' => $templateData,
            'price' => $price['cells']['general'],
            'infoblocks' => $infoblocks,
            'provider' => $provider,
            'recipient' => $recipient,

            'link' => $link,
            '$base64File' => $base64File,
            // 'measure' => $measure,
            // $texts,
            // $quantity
            // '$applyStyle' => $applyStyle,
            // '$clls' => $clls
            // '$html' =>  $html

        ]);
    }



    public static function getMonthTitleAccusative($number)
    {
        // Винительный падеж
        $lastDigit = $number % 10;
        $lastTwoDigits = $number % 100;

        if ($lastDigit === 1 && $lastTwoDigits !== 11) {
            return $number . " месяц"; // Например, 1 месяц
        } else if (($lastDigit === 2 || $lastDigit === 3 || $lastDigit === 4) && !($lastTwoDigits >= 12 && $lastTwoDigits <= 14)) {
            return $number . " месяца"; // Например, 2 месяца, 3 месяца, 4 месяца
        } else {
            return $number . " месяцев"; // Для остальных случаев, например, 5 месяцев, 11 месяцев
        }
    }

    public static function getFullMeasureValue($measure, $quantity)
    {
        $resultMeasure = '';
        $abonsAndLicenzQantity = 6;
        $pureMeasure = '';


        switch ($measure) {

            case MON:
                if ($quantity) {
                    $pureMeasure = FileController::getMonthTitleAccusative($quantity);
                    $resultMeasure = $pureMeasure;
                }
                return $resultMeasure;

            case ABON_6:
                $abonsAndLicenzQantity = 6;
                $pureMeasure = FileController::getMonthTitleAccusative(6);
                $resultMeasure = "за абонемент на " . $pureMeasure;
                return $resultMeasure;

            case ABON_12:
                $abonsAndLicenzQantity = 12;
                $pureMeasure = FileController::getMonthTitleAccusative(12);
                $resultMeasure = "за абонемент на " . $pureMeasure;
                return $resultMeasure;

            case ABON_24:
                $abonsAndLicenzQantity = 24;
                $pureMeasure = FileController::getMonthTitleAccusative(24);
                $resultMeasure = "за абонемент на " . $pureMeasure;
                return $resultMeasure;

            case LIC_6:
                $abonsAndLicenzQantity = 6;
                $pureMeasure = FileController::getMonthTitleAccusative(6);
                $resultMeasure = "за лицензию на " . $pureMeasure;
                return $resultMeasure;

            case LIC_12:
                $abonsAndLicenzQantity = 12;
                $pureMeasure = FileController::getMonthTitleAccusative(12);
                $resultMeasure = "за лицензию на " . $pureMeasure;
                return $resultMeasure;

            case LIC_24:
                $abonsAndLicenzQantity = 24;
                $pureMeasure = FileController::getMonthTitleAccusative(24);
                $resultMeasure = "за лицензию на " . $pureMeasure;
                return $resultMeasure;

            default:
                return $resultMeasure;
        }
    }

    // public function generateWord(Request $request)
    // {
    //     // Используйте $request для получения данных для вставки в документ Word.
    //     $data = $request->get('data');

    //     $phpWord = new PhpWord();

    //     // Здесь создайте свой документ с использованием $data
    //     // ...

    //     $filename = time() . '.docx';
    //     $path = storage_path('app/public/uploads/' . $filename);

    //     $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
    //     $objWriter->save($path);

    //     return response()->json(['message' => 'Word document generated', 'file' => $filename]);
    // }
}
