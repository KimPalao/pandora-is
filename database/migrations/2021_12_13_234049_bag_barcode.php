<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BagBarcode extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bags', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique();
        });
        $faker = \Faker\Factory::create();
        foreach (DB::table('bags')->whereNull('barcode')->get() as $bag) {
            DB::table('bags')
                ->where('id', $bag->id)
                ->update(['barcode' => $faker->numerify('##########')]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bags', function (Blueprint $table) {
            $table->dropColumn(['barcode']);
        });
    }
}
