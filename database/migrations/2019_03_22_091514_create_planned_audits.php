<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePlannedAudits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('planned_audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('checklistId');
            $table->foreign('checklistId')
                ->references('id')
                ->on('checklists');
            $table->uuid('planId');
            $table->foreign('planId')
                ->references('id')
                ->on('inspection_plans');
            $table->date("date");
            $table->time('startTime')->nullable();
            $table->time('endTime')->nullable();
            $table->boolean('wasExecuted')->default(0);
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
        Schema::dropIfExists('planned_audits');
    }
}
