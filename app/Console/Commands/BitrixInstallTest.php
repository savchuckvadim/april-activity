<?php

namespace App\Console\Commands;

use App\Http\Controllers\AdminOuter\ListController;
use App\Http\Controllers\BitrixController;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class BitrixInstallTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'btx:install';

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
        // $domain = 'april-garant.bitrix24.ru';
        // $method = '/crm.activity.configurable.add.json';
        // $token = 'AKfycbwhWSztCxZ1G_gw4lqKsMb4Fv5nHVBMM84T0mgI0yjozDaVUSFx_ouNuvck9v1FFKk_Sw';

        // $url = 'https://script.google.com/macros/s/' . $token . '/exec';

        // $response = Http::get($url);

        $response = ListController::setLists();

        $this->line($response);
    }
}
