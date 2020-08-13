<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_sites', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('project_id');
            $table->string('site_id');
            $table->string('survey_date');
            $table->string('status')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_sites');
    }
}
