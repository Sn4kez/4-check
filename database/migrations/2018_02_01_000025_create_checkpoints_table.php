<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckpointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checkpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('prompt');
            $table->text('description')->nullable(true);
            $table->boolean('mandatory')->default(false);
            $table->float('factor')->default(1.0);
            $table->string('index');
            $table->uuid('scoringSchemeId');
            $table->foreign('scoringSchemeId')
                ->references('id')
                ->on('scoring_schemes');
            $table->uuid('evaluationSchemeId');
            $table->string('evaluationSchemeType');
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
        Schema::dropIfExists('checkpoints');
    }
}
