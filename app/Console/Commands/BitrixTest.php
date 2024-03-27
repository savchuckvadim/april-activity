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
        $method = '/crm.activity.add.json';
        $hook = BitrixController::getHook($domain);
        $dealId = 11758;

        $fields =
            [
                "OWNER_TYPE_ID" => 2,
                "OWNER_ID" => $dealId,
                "PROVIDER_ID" => 'REST_APP',
                "PROVIDER_TYPE_ID" => 'LINK',
                "SUBJECT" => "Новое дело",
                "COMPLETED" => "N",
                "RESPONSIBLE_ID" => 1,
                "DESCRIPTION" => "Описание нового дела"
            ];

        $data = ['fields' => $fields];
        $url = $hook . $method;
        // $response = Http::withHeaders([
        //     'Accept' => 'application/json',
        //     'content-type' => 'application/json',
        //     'X-Requested-With' => 'XMLHttpRequest'
        // ])->get($url, $data);
        $response = Http::get($url, $data);


        $this->line($hook);

        // } else {
        //     $this->error("Ошибка запроса! Статус: " . $response->status());
        //     $this->line("Ответ: " . $response->body());
        // }
    }
}
