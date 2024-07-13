<?php

namespace App\Console\Commands;

use App\Http\Controllers\BitrixController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BitrixRPATest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'btx:rpa';

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
        $domain = 'april-dev.bitrix24.ru';
        // $method = '/rpa.stage.listForType.json';
        $method = '/rpa.stage.add.json';
        
        // $hook = env('TEST_HOOK');
        $hook = BitrixController::getHook($domain);
      

        $data = [
            "typeId" => 43,

            
        ];
        $url = $hook . $method;

        $response = Http::get($url, $data);

        $this->line($response->body());
    }
}
