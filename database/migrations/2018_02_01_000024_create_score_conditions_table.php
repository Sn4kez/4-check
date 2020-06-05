<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScoreConditionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('score_conditions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->float('from')->nullable();
            $table->float('to')->nullable();
            $table->uuid('scoreId');
            $table->foreign('scoreId')
                ->references('id')
                ->on('scores');
            $table->uuid('valueSchemeId');
            $table->foreign('valueSchemeId')
                ->references('id')
                ->on('value_schemes')
                ->onDelete('cascade');
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
        Schema::dropIfExists('score_conditions');
    }
}
