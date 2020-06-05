<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('userId');
            $table->foreign('userId')
                ->references('id')
                ->on('users');
            $table->uuid('companyId')->nullable();
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->uuid('checklistId')->nullable();
            $table->foreign('checklistId')
                ->references('id')
                ->on('checklists')
                ->onDelete('cascade');
            $table->uuid('stateId')->nullable();
            $table->foreign('stateId')
                ->references('id')
                ->on('audit_states')
                ->onDelete('cascade');
            $table->boolean('isArchived')->default(0);
            $table->timestamp('executionAt')->nullable();
            $table->timestamp('executionDue')->nullable();
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
        Schema::dropIfExists('audits');
    }
}
