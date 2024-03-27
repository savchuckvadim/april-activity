<?php

namespace App\Console\Commands;

use App\Http\Controllers\BitrixController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BitrixTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'btx:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domain = 'april-garant.bitrix24.ru';
        $method = '/crm.activity.configurable.add.json';
        // $hook = env('TEST_HOOK');
        $hook = BitrixController::getHook($domain);
        $dealId = 12202;

        $fields =
            [
               
                "TYPE_ID" => 2, //из метода crm.enum.activitytype
                "COMMUNICATIONS" => [
                    [
                        'VALUE' => "+79832322323",
                        'ENTITY_ID' => $dealId,
                        'ENTITY_TYPE_ID' => 2
                    ]
                ], //где 134 - id контакта, 3 - тип "контакт"
                "SUBJECT" => "Новый звонок",
                // "START_TIME"=> dateStr,
                // "END_TIME"=> dateStr,
                "COMPLETED" => "N",
                "PRIORITY" => 3, //из метода crm.enum.activitypriority
                "RESPONSIBLE_ID" => 1,
                "DESCRIPTION" => "Важный звонок",
                "DESCRIPTION_TYPE" => 3, //из метода crm.enum.contenttype
                "DIRECTION" => 2, // из метода crm.enum.activitydirection
                "WEBDAV_ELEMENTS" =>
                [
                    'fileData' => 'https://april-online.ru/storage/clients/april-garant.bitrix24.ru/documents/194/271103-27_0324.pdf'
                ],
                // "FILES":
                // [
                //     { fileData: document.getElementById('file1') }
                // ] //после установки модуля disk и конвертации данных из webdav можно будет указавать FILES вместо WEBDAV_ELEMENTS
            ];
        $layout = [
            "icon" => [
                "code" => "call-completed"
            ],
            "header" => [
                "title" => "Входящий звонок",
                "tags" => [
                    "status2" => [
                        "type" => "warning",
                        "title" => "не расшифрован"
                    ]
                ]
            ],
            "body" => [
                "logo" => [
                    "code" => "call-incoming",
                    "action" => [
                        "type" => "redirect",
                        "uri" => "/crm/deal/details/123/"
                    ]
                ],
                "blocks" => [
                    "client" => [
                        "type" => "withTitle",
                        "properties" => [
                            "title" => "Клиент",
                            "inline" => true,
                            "block" => [
                                "type" => "text",
                                "properties" => [
                                    "value" => "ООО Рога и Копыта"
                                ]
                            ]
                        ]
                    ],
                    "responsible" => [
                        "type" => "lineOfBlocks",
                        "properties" => [
                            "blocks" => [
                                "client" => [
                                    "type" => "link",
                                    "properties" => [
                                        "text" => "Сергей Востриков",
                                        "bold" => true,
                                        "action" => [
                                            "type" => "redirect",
                                            "uri" => "/crm/lead/details/789/"
                                        ]
                                    ]
                                ],
                                "phone" => [
                                    "type" => "text",
                                    "properties" => [
                                        "value" => "+7 999 888 7777"
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            "footer" => [
                "buttons" => [
                    "startCall" => [
                        "title" => "О клиенте",
                        "action" => [
                            "type" => "openRestApp",
                            "actionParams" => [
                                "clientId" => 456
                            ]
                        ],
                        "type" => "primary"
                    ]
                ],
                "menu" => [
                    "showPostponeItem" => "false",
                    "items" => [
                        "confirm" => [
                            "title" => "Подтвердить заявку",
                            "action" => [
                                "type" => "restEvent",
                                "id" => "confirm",
                                "animationType" => "loader"
                            ]
                        ],
                        "decline" => [
                            "title" => "Отклонить заявку",
                            "action" => [
                                "type" => "restEvent",
                                "id" => "decline",
                                "animationType" => "loader"
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $data = [
            "ownerTypeId" => 2,
            "ownerId" => $dealId,
            'fields' => $fields,
            'layout' => $layout,
            
        ];
        $url = $hook . $method;
        // $response = Http::withHeaders([
        //     'Accept' => 'application/json',
        //     'content-type' => 'application/json',
        //     'X-Requested-With' => 'XMLHttpRequest'
        // ])->get($url, $data);
        $response = Http::get($url, $data);


        $this->line($response->body());

        // } else {
        //     $this->error("Ошибка запроса! Статус: " . $response->status());
        //     $this->line("Ответ: " . $response->body());
        // }
    }
}
