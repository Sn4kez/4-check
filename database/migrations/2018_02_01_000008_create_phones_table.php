<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('userId');
            $table->foreign('userId')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->string('countryCode', 8);
            $table->string('nationalNumber', 64);
            $table->string('typeId', 16);
            $table->foreign('typeId')
                ->references('id')
                ->on('phone_types');
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
        Schema::dropIfExists('phones');
    }
}
