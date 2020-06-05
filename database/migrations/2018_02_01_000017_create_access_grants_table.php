<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccessGrantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('access_grants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('subjectId');
            $table->string('subjectType');
            $table->uuid('objectId');
            $table->string('objectType');
            $table->boolean('view');
            $table->boolean('index');
            $table->boolean('update');
            $table->boolean('delete');
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
        Schema::dropIfExists('access_grants');
    }
}
