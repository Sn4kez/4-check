<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Ramsey\Uuid\Uuid;

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
            $table->uuid('id')->primary();
            $table->uuid('typeId')->nullable(true);
            $table->foreign('typeId')
                ->references('id')
                ->on('task_types');
            $table->uuid('stateId')->nullable(true);
            $table->foreign('stateId')
                ->references('id')
                ->on('task_states');
            $table->uuid('priorityId')->nullable(true);
            $table->foreign('priorityId')
                ->references('id')
                ->on('task_priorities');
            $table->uuid('issuerId');
            $table->foreign('issuerId')
                ->references('id')
                ->on('users');
            $table->uuid('assigneeId');
            $table->foreign('assigneeId')
                ->references('id')
                ->on('users');
            $table->uuid('locationId')->nullable(true);
            $table->foreign('locationId')
                ->references('id')
                ->on('locations');
            $table->string('name', 128);    
            $table->text('description')->nullable(true);
            $table->boolean('giveNotice')->nullable(true);
            $table->uuid('companyId');
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamp('createdAt');
            $table->timestamp('updatedAt');
            $table->timestamp('deletedAt')->nullable(true);
            $table->timestamp('assignedAt')->nullable(true);
            $table->timestamp('doneAt')->nullable(true);
            $table->mediumText('image')->nullable(true);
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
