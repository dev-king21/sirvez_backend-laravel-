<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStikersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stikers', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('category_id');
            $table->string('name');
            $table->string('stiker_img');
            $table->string('user_id')->nullable();
            $table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stikers');
    }
}
