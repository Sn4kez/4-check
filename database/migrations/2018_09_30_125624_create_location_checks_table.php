<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationChecksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('checkId');
            $table->foreign('checkId')
                ->references('id')
                ->on('checks')
                ->onDelete('cascade');
            $table->uuid('locationId')->nullable();
            $table->foreign('locationId')
                ->references('id')
                ->on('locations')
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
        Schema::dropIfExists('location_checks');
    }
}
