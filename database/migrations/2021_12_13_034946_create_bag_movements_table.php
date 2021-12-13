<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBagMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bag_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bag_id');
            // Null "From" means the bag was just obtained
            $table->unsignedBigInteger('from')->nullable();
            // Null "To" means the bag was sold
            $table->unsignedBigInteger('to')->nullable();
            $table->dateTime('datetime');
            $table->foreign('from')->references('id')->on('sites');
            $table->foreign('to')->references('id')->on('sites');
            $table->foreign('bag_id')->references('id')->on('bags');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bag_movements');
    }
}
