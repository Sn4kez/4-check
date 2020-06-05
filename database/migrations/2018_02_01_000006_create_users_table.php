<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('email', 128)->unique();
            $table->boolean('emailVerified')->default(0);
            $table->string('password');
            $table->string('firstName', 128)->nullable();
            $table->string('middleName', 128)->nullable();
            $table->string('lastName', 128)->nullable();
            $table->string('genderId', 16);
            $table->foreign('genderId')->references('id')->on('genders');
            $table->uuid('companyId');
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->string('roleId', 16)->default('user');
            $table->foreign('roleId')->references('id')->on('roles');
            $table->string('localeId', 16)->default(config('app.locale'));
            $table->foreign('localeId')->references('id')->on('locales');
            $table->string('timezone', 64)->default(config('app.timezone'));
            $table->boolean('isActive')->default(1);
            $table->timestamp('lastLogin')->nullable();
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
        Schema::dropIfExists('users');
    }
}
