<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Seeder;




class users extends Seeder
{
    function __construct() {
        
    }
   
    
    /**
     * Run the database seeds.
     *
     * @return void
     */
   
    public function run()
    { 

      return User::factory()->count(500)->create();
    }
}
