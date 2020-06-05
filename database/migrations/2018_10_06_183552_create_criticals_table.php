<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCriticalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('criticals', function (Blueprint $table) {
            $table->uuid('score_id');
            $table->foreign('score_id')
                ->references('id')
                ->on('scores');
            $table->string('critical_type');
            $table->string('critical_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('critical_notification');
    }
}
