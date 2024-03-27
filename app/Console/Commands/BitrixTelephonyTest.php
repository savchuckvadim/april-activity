<?php

namespace App\Console\Commands;

use App\Http\Controllers\BitrixController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BitrixTelephonyTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:name';

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
        $url = $hook . $method;
        $data = null;
        $response = Http::get($url, $data);


        $this->line($response->json());
    }
}
