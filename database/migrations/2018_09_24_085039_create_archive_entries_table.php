<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArchiveEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('archive_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parentId');
            $table->foreign('parentId')
                ->references('id')
                ->on('archive_directories')
                ->onDelete('cascade');
            $table->uuid('referenceId')->nullable();
            $table->uuid('objectId');
            $table->string('objectType');
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
        Schema::dropIfExists('archive_entries');
    }
}
