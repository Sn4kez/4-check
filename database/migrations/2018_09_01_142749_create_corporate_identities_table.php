<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorporateIdentitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporate_identities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->string('brand_primary')->nullable();
            $table->string('brand_secondary')->nullable();
            $table->string('link_color')->nullable();
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
        Schema::dropIfExists('corporate_identities');
    }
}
