<?php

namespace App\Console\Commands;

use App\Http\Controllers\GoogleController;
use Illuminate\Console\Command;

class CreateGoogleDocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'google:document-create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates a new Google Document';

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
        $data = null;
        $googleController = new GoogleController();
        $result = $googleController->documentCreate($data);
        $this->info('Google Document has been created successfully.' . $result);
    }
}
