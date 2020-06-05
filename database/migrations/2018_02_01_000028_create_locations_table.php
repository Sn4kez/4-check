<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('typeId');
            $table->foreign('typeId')
                ->references('id')
                ->on('location_types');
            $table->uuid('stateId')->nullable(true);
            $table->foreign('stateId')
                ->references('id')
                ->on('location_states');
            $table->string('name', 128);
            $table->text('description')->nullable();
            $table->string('street', 128)->nullable();
            $table->string('streetNumber', 6)->nullable();
            $table->string('city', 128)->nullable();
            $table->string('postalCode', 128)->nullable();
            $table->string('province', 128)->nullable();
            $table->string('countryId', 8);
            $table->foreign('countryId')
                ->references('id')
                ->on('countries');
            $table->uuid('companyId');
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
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
        Schema::dropIfExists('locations');
    }
}
