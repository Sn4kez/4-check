<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePictureChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('picture_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('checkId');
            $table->foreign('checkId')
                ->references('id')
                ->on('checks')
                ->onDelete('cascade');

            $table->text('value')->nullable();

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
        Schema::dropIfExists('picture_checks');
    }
}
