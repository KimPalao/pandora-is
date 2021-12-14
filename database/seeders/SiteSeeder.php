<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i  = 0; $i < 10; $i++) {
            $faker = \Faker\Factory::create();
            DB::table('sites')->insert([
                'name' => $faker->city()
            ]);
        }
    }
}
