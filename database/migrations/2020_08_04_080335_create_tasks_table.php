<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('task');
            $table->string('company_id')->nullable();
            $table->string('project_id')->nullable();
            $table->string('site_id')->nullable();
            $table->string('room_id')->nullable();
            $table->string('due_by_date')->nullable();
            $table->string('task_img')->nullable();
            $table->string('priority')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('tasks');
    }
}
