<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('company_id');
            $table->string('project_id');
            $table->string('site_id');
            $table->string('department_id');
            $table->string('building_id');
            $table->string('floor_id');
            $table->string('room_number')->nullable();
            $table->string('ceiling_height')->nullable();
            $table->string('ceiling')->nullable();
            $table->string('wall')->nullable();
            $table->string('asbestos')->nullable();
            $table->string('notes')->nullable();
            $table->string('estimate_day')->nullable();
            $table->string('estimate_time')->nullable();
            $table->string('signed_off')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('completed_by')->nullable();
            $table->string('completed_date')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rooms');
    }
}
