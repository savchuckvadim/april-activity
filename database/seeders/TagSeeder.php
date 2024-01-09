<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    static $tags = [
        'Adventure', 'Arts', 'Fashion',
        'Health', 'People', 'History'
    ];


    public function run()
    {
        foreach (self::$tags as $tag) {
            DB::table('tags')->insert([
                'name' => $tag
                
            ]);
        }
    }
}
