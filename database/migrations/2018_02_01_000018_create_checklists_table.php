<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('description')->nullable();
            $table->uuid('companyId')->nullable();
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->uuid('createdBy')->nullable(true);
            $table->foreign('createdBy')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->uuid('lastUpdatedBy')->nullable(true);
            $table->foreign('lastUpdatedBy')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->uuid('lastUsedBy')->nullable(true);
            $table->foreign('lastUsedBy')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('icon')->nullable();
            $table->boolean('chooseRandom')->default(false);
            $table->integer('numberQuestions')->default(0);
            $table->boolean('needsApproval')->default(0);
            $table->timestamp('usedAt')->nullable();
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
        Schema::dropIfExists('checklists');
    }
}
