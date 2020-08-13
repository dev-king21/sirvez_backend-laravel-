<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer('company_id');
            $table->string('site_name')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('site_instructions')->nullable();
            $table->string('parking_instructions')->nullable();
            $table->string('access_hour')->nullable();
            $table->text('comment')->nullable();
            $table->integer('status')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer("updated_by")->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sites');
    }
}
