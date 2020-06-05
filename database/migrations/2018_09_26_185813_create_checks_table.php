<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('auditId');
            $table->foreign('auditId')
                ->references('id')
                ->on('audits')
                ->onDelete('cascade');
            $table->uuid('checklistId');
            $table->foreign('checklistId')
                ->references('id')
                ->on('checklists')
                ->onDelete('cascade');
            $table->string('parentType')->nullable();
            $table->uuid('parentId')->nullable();
            $table->uuid('sectionId')->nullable();
            $table->foreign('sectionId')
                ->references('id')
                ->on('sections')
                ->onDelete('cascade');
            $table->uuid('checkpointId')->nullable();
            $table->foreign('checkpointId')
                ->references('id')
                ->on('checkpoints')
                ->onDelete('cascade'); 
            $table->uuid('valueSchemeId')->nullable();
            $table->foreign('valueSchemeId')
                ->references('id')
                ->on('value_schemes')
                ->onDelete('cascade');
            $table->uuid('scoringSchemeId')->nullable();
            $table->foreign('scoringSchemeId')
                ->references('id')
                ->on('scoring_schemes')
                ->onDelete('cascade');
            $table->uuid('evaluationSchemeId')->nullable(); 
            $table->date('executionDate')->nullable();
            $table->float('rating')->nullable();
            $table->string('objectType');
            $table->uuid('objectId');
            $table->string('valueType')->nullable();
            $table->uuid('valueId')->nullable();
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
        Schema::dropIfExists('checks');
    }
}
