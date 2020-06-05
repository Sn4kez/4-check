<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateValueChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('value_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->float('value')->nullable();
            $table->uuid('checkId');
            $table->foreign('checkId')
                ->references('id')
                ->on('checks')
                ->onDelete('cascade');
            $table->uuid('scoreId')->nullable();
            $table->foreign('scoreId')
                ->references('id')
                ->on('scores')
                ->onDelete('cascade');
            $table->timestamp('createdAt');
            $table->timestamp('updatedAt');
            $table->timestamp('deletedAt')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('value_checks');
    }
}
