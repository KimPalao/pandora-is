<?php

namespace Database\Seeders;

use DateInterval;
use Facade\Ignition\LogRecorder\LogMessage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BagMovementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (DB::table('bags')->get() as $bag) {
            $movement_count = random_int(1, 10);
            $from = null;
            $to = null;
            $days = 0;
            $date = new \DateTimeImmutable($bag->date_obtained);
            for ($i = 0; $i < $movement_count; $i++) {
                $minutes = random_int(0, 24 * 60 - 1);
                if ($i === 0) {
                    // Just obtained
                    /** @var \App\Models\Site */
                    $to = DB::table('sites')->inRandomOrder()->first();
                    DB::table('bag_movements')->insert([
                        'to' => $to->id,
                        'datetime' => $date->format('Y-m-d'),
                        'bag_id' => $bag->id,
                    ]);
                } else if ($i === $movement_count - 1 && $bag->is_sold) {
                    /** @var \App\Models\Site */
                    $from = $to;
                    Log::debug("P{$days}D{$minutes}M");
                    DB::table('bag_movements')->insert([
                        'from' => $from->id,
                        'datetime' => ($date->add(new DateInterval("P{$days}DT{$minutes}M")))->format('Y-m-d H:i:s'),
                        'bag_id' => $bag->id,
                    ]);
                } else {
                    /** @var \App\Models\Site */
                    $from = $to;
                    /** @var \App\Models\Site */
                    $to = DB::table('sites')->inRandomOrder()->first();
                    DB::table('bag_movements')->insert([
                        'from' => $from->id,
                        'to' => $to->id,
                        'datetime' => ($date->add(new DateInterval("P{$days}DT{$minutes}M")))->format('Y-m-d H:i:s'),
                        'bag_id' => $bag->id,
                    ]);
                }
                $days += random_int(1, 7);
            }
        }
    }
}
