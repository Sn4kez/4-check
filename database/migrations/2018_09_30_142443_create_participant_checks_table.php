<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participant_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('checkId');
            $table->foreign('checkId')
                ->references('id')
                ->on('checks')
                ->onDelete('cascade');
            $table->uuid('participantId')->nullable();
            $table->foreign('participantId')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('externalParticipant')->nullable();
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
        Schema::dropIfExists('participant_checks');
    }
}
