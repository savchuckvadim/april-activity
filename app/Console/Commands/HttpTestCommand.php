<?php

namespace App\Console\Commands;

use App\Models\Garant\Infoblock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class HttpTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:infoblocks';

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
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'content-type' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest'
        ])->get('http://april-online.ru/api/infoblocks');


        if ($response->successful()) {
            $infoblocks = $response->json();
            foreach ($infoblocks['infoblocks'] as $infoblockData) {
                $iblock = Infoblock::create([
                    'number' => $infoblockData['number'],
                    'name' => $infoblockData['name'],
                    'code' => $infoblockData['code'],
                    'title' => $infoblockData['title'],
                    'description' => $infoblockData['description'],
                    'descriptionForSale' => $infoblockData['descriptionForSale'],
                    'shortDescription' => $infoblockData['shortDescription'],
                    'weight' => $infoblockData['weight'],
                    'inGroupId' => $infoblockData['inGroupId'],
                    'groupId' => $infoblockData['groupId'],
                    'isLa' => $infoblockData['isLa'],
                    'isFree' => $infoblockData['isFree'],
                    'isShowing' => $infoblockData['isShowing'],
                    'isSet' => $infoblockData['isSet'],
                ]);
                $this->line($iblock['code']);
            }
        } else {
            $this->error("Ошибка запроса! Статус: " . $response->status());
            $this->line("Ответ: " . $response->body());
        }
    }
}
