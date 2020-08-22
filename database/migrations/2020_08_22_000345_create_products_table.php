<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('product_name');
            $table->string('room_id');
            $table->string('description')->nullable();
            $table->string('qty')->nullable();
            $table->string('action')->nullable();
            $table->string('to_site_id')->nullable();
            $table->string('to_room_id')->nullable();
            $table->string('upload_file')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updateded_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
