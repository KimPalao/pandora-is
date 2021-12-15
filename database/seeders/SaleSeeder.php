<?php

namespace Database\Seeders;

use App\Models\Bag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (Bag::doesnthave('sale')->where('is_sold', true)->get() as $bag) {
            /** @var Bag $bag */
            $deviation = (random_int(-10, 10) * $bag->price) / 100;
            if ($sold_movement = DB::table('bag_movements')->where('bag_id', $bag->id)->whereNull('to')->first()) {
                DB::table('sales')->insert([
                    'price' => $bag->price + $deviation,
                    'datetime' => $sold_movement->datetime,
                    'bag_id' => $bag->id
                ]);
            }
        }
    }
}
