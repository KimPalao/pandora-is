<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBagImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bag_images', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_name');
            $table->unsignedBigInteger('bag_id');
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
        Schema::dropIfExists('bag_images');
    }
}
