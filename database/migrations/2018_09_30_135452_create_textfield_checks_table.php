<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTextfieldChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('textfield_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->text('value')->nullable();
            $table->uuid('checkId');
            $table->foreign('checkId')
                ->references('id')
                ->on('checks')
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
        Schema::dropIfExists('textfield_checks');
    }
}
