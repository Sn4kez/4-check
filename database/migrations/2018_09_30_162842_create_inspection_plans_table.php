<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInspectionPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->uuid('userId');
            $table->foreign('userId')
                ->references('id')
                ->on('users');
            $table->uuid('checklistId');
            $table->foreign('checklistId')
                ->references('id')
                ->on('checklists');
            $table->uuid('companyId');
            $table->foreign('companyId')
                ->references('id')
                ->on('companies');
            $table->enum('type', ['monthly', 'weekly','daily', 'hourly']);
            $table->integer('factor');
            $table->date('startDate')->nullable();
            $table->date('endDate')->nullable();
            $table->time('startTime');
            $table->time('endTime');
            $table->boolean('monday')->default(1);
            $table->boolean('tuesday')->default(1);
            $table->boolean('wednesday')->default(1);
            $table->boolean('thursday')->default(1);
            $table->boolean('friday')->default(1);
            $table->boolean('saturday')->default(1);
            $table->boolean('sunday')->default(1);
            $table->date('lastGeneratedDate')->nullable();
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inspection_plans');
    }
}
