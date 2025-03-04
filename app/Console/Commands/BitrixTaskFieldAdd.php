<?php

namespace App\Console\Commands;

use App\Http\Controllers\BitrixController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BitrixTaskFieldAdd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    // protected $signature = 'btx_task:field_add';
    // protected $signature = 'btx_task:fields';
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
        $domain = 'gsr.bitrix24.ru';
        // $method = '/task.item.userfield.getlist';
        $method = '/task.item.userfield.add';

        // $hook = env('TEST_HOOK');
        $hook = BitrixController::getHook($domain);


        $data = [
            'PARAMS' => [
                'USER_TYPE_ID' => 'string',
                'FIELD_NAME' => 'TASK_EVENT_COMMENT',
                'XML_ID' => 'TASK_EVENT_COMMENT',
                'LABEL' => 'Комментарий'
            ]


        ];
        $url = $hook . $method;

        $response = Http::get($url);

        $this->line($response->body());
    }
}
