<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLocationExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_extensions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('locationId')->nullable();
            $table->foreign('locationId')
                ->references('id')
                ->on('locations')
                ->onDelete('cascade');
            $table->boolean('fixed');
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
        Schema::dropIfExists('location_extensions');
    }
}
