<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->string('typeId', 16);
            $table->foreign('typeId')
                ->references('id')
                ->on('address_types');
            $table->string('line1', 128)->nullable();
            $table->string('line2', 128)->nullable();
            $table->string('city', 128)->nullable();
            $table->string('postalCode', 128)->nullable();
            $table->string('province', 128)->nullable();
            $table->string('countryId', 8);
            $table->foreign('countryId')
                ->references('id')
                ->on('countries');
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
        Schema::dropIfExists('addresses');
    }
}
