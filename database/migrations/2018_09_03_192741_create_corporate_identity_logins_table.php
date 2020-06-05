<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCorporateIdentityLoginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('corporate_identity_logins', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('corporateId')->unique();
            $table->foreign('corporateId')
                ->references('id')
                ->on('corporate_identities')
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
        Schema::dropIfExists('corporate_identity_logins');
    }
}
