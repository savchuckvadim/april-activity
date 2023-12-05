<?php

namespace App\Http\Controllers;

use App\Models\Template;
use CRest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\TemplateProcessor;
use Ramsey\Uuid\Uuid;

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


        $templatePath = Template::getTemplatePath($templateData['id']);
        // storage_path() . '/app/public/description/general/Description';
        $resultPath = storage_path('app/public/clients/' . $domain . '/documents/' . $userId);

        // Проверяем, существует ли исходный файл
        if (!file_exists($resultPath)) {
            mkdir($resultPath, 0777, true); // Создаем директорию с правами 0777 рекурсивно
        }

        try {
            $template = new TemplateProcessor($templatePath);

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
            $section = $phpWord->addSection();
            $priceData = $price['cells']['general'][0]['cells'];
            $cells = [];
            $html = '<table style="border-collapse: collapse;">';
            foreach ($priceData as $row) {
                if ($row['isActive']) {
                    $html .= '<tr>';
                    // Добавьте ячейки для каждого активного элемента
                    // Например: $html .= '<td>' . htmlspecialchars($row->name) . '</td>';
                    $html .= '<td style="border: 1px solid black;">' . htmlspecialchars($row['value']) . '</td>';
                    $html .= '</tr>';
                }
            }

            $html .= '</table>';
            \PhpOffice\PhpWord\Shared\Html::addHtml($section, $html);
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($resultPath . '/' . $resultFileName);


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
            'templatePath' =>  $templatePath,
            '$resultPath' => $resultPath,
            'template' => $templateData,
            'price' => $price,
            'infoblocks' => $infoblocks,
            'provider' => $provider,
            'recipient' => $recipient,
            'priceData' => $priceData,
            'link' => $link,
            '$base64File' => $base64File,
            '$html' =>  $html

        ]);
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
