<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('notice_type')->nullable();
            $table->integer('notice_id')->nullable();
            $table->text('notification')->nullable();
            $table->integer('created_by')->nullable();
            $table->string('created_date')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('is_read')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
