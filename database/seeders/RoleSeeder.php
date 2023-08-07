<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    static $roles = [
        'Admin', 'Advertiser', 'Master'
    ];


    public function run()
    {
        foreach (self::$roles as $role) {
            DB::table('roles')->insert([
                'name' => $role
                
            ]);
        }
    }
}
