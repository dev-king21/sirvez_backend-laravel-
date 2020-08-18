<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('company_id');
            $table->string('building_id')->nullable();
            $table->string('floor_id')->nullable();
            $table->string('project_name');
            $table->string('user_id')->nullable();
            $table->string('manager_id')->nullable();
            $table->string('contact_number')->nullable();
            $table->date('survey_start_date')->nullable();
            $table->string('created_by')->nullable();
            $table->string('project_summary')->nullable();
            $table->string('upload_doc')->nullable();   
            $table->string('status')->nullable();   
            $table->string('signed_off')->nullable();   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
