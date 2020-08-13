<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('situation')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('department')->nullable();
            $table->string('mobile')->nullable();
            $table->string('company_id')->nullable();
            $table->string('company_name')->nullable();
            $table->string('extension')->nullable();
            $table->string('telephone')->nullable();
            $table->integer('status')->default(1);
            $table->integer('user_type')->nullable();
            $table->integer('prefer')->nullable();
            $table->string('invite_code')->nullable();
            $table->string('profile_pic')->nullable();
            $table->float('rate')->nullable();
            $table->text('auth_token')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
