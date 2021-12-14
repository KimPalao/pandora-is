<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\DateFactory;
use Illuminate\Support\Facades\DB;

class BagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create('fr_FR');
        $date = new \DateTime('2000-01-01');
        $now = (new \DateTime())->format('Y-m-d H:i:s');
        $days = random_int(0, 365 * 20);
        $date->add(new \DateInterval("P{$days}D"));
        DB::table('bags')->insert([
            'name' => $faker->name(),
            'price' => random_int(10, 100) * 100,
            'date_obtained' => $date->format('Y-m-d'),
            'is_sold' => random_int(0, 1),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
