<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Google\Client;
use Google\Service\Docs;
use Google\Service\Drive;

class GoogleController extends Controller
{
    public static function documentCreate()
    {

        $client = new Client();
        $driveService = new Drive($client);
        $client->setApplicationName('Ваше приложение');
        $client->setScopes([
            Docs::DOCUMENTS,
            Drive::DRIVE // Добавьте этот scope
        ]);
        $client->setAuthConfig(env('GOOGLE_DOCS_PATH'));

        $service = new Docs($client);

        // Создание нового документа
        $document = new Docs\Document();
        $document->setTitle('Новый документ');
        $createdDocument = $service->documents->create($document);

        // Получение ID созданного документа
        $documentId = $createdDocument->documentId;


        $requests = [
            new Docs\Request([
                'insertText' => [
                    'location' => [
                        'index' => 1,
                    ],
                    'text' => "Заголовок документа\n"
                ]
            ]),
            new Docs\Request([
                'updateTextStyle' => [
                    'range' => [
                        'startIndex' => 1,
                        'endIndex' => 20,
                    ],
                    'textStyle' => [
                        'bold' => true,
                        'fontSize' => [
                            'magnitude' => 24,
                            'unit' => 'PT'
                        ],
                    ],
                    'fields' => 'bold,fontSize'
                ]
            ]),
        ];

        $batchUpdateRequest = new Docs\BatchUpdateDocumentRequest([
            'requests' => $requests
        ]);

        $response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);


        $requests = [
            new Docs\Request([
                'insertTable' => [
                    'rows' => 3,
                    'columns' => 3,
                    'location' => [
                        'index' => 21, // Добавить после текста
                    ],
                ]
            ])
            // Здесь могут быть дополнительные запросы для заполнения таблицы
        ];

        $batchUpdateRequest = new Docs\BatchUpdateDocumentRequest([
            'requests' => $requests
        ]);

        $response = $service->documents->batchUpdate($documentId, $batchUpdateRequest);










        // Формирование URL-адреса для доступа к документу
        $documentUrl = 'https://docs.google.com/document/d/' . $documentId . '/edit';
        $permission = new Drive\Permission();

        $permission->setType('anyone');
        $permission->setRole('writer');




        try {
            $driveService->permissions->create($documentId, $permission);
            echo "Разрешение на доступ добавлено.";
        } catch (Exception $e) {
            echo 'Произошла ошибка: ',  $e->getMessage(), "\n";
        }
        return $documentUrl;
    }
}
