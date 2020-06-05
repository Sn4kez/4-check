<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participant_extensions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('userId')->nullable();
            $table->foreign('userId')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->boolean('fixed');
            $table->text('external')->nullable();
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
        Schema::dropIfExists('participant_extensions');
    }
}
