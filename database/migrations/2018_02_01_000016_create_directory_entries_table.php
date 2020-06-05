<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDirectoryEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('directory_entries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('parentId');
            $table->foreign('parentId')
                ->references('id')
                ->on('directories')
                ->onDelete('cascade');
            $table->uuid('objectId');
            $table->string('objectType');
            $table->boolean('archived')->default(0);
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
        });
    }

    /**morjp
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('directory_entries');
    }
}
