<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BagBarcodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();
        foreach (DB::table('bags')->whereNull('barcode')->get() as $bag) {
            DB::table('bags')
                ->where('id', $bag->id)
                ->update(['barcode' => $faker->numerify('##########')]);
        }
    }
}
