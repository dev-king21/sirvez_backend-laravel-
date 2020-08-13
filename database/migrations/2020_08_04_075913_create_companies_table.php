<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->integer("manager")->nullable();
            $table->string('name');
            $table->string('sagerref')->nullable();
            $table->string('user_name')->nullable();
            $table->string('image')->nullable();
            $table->string('logo_img')->nullable();
            $table->integer('status')->nullable();
            $table->string('bg_image')->nullable();
            $table->string('website')->nullable();
            $table->string('company_email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postcode')->nullable();
            $table->string('country')->nullable();
            $table->string('company_type')->nullable();
            $table->string('telephone')->nullable();
            $table->string('site_url')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
}
