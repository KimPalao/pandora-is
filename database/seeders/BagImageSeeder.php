<?php

namespace Database\Seeders;

use App\Models\Bag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BagImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        foreach (Bag::doesnthave('images')->get() as $bag) {
            for ($i = 0; $i < 3; $i++) {
                $image = $faker->image('public/img/bags');
                $image = substr($image, 7);
                DB::table('bag_images')->insert([
                    'name' => "{$bag->name}-$i",
                    'file_name' => $image,
                    'bag_id' => $bag->id
                ]);
            }
        }
    }
}
