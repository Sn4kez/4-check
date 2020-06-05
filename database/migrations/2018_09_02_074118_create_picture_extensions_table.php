<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePictureExtensionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('picture_extensions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->mediumText('image')->nullable();
            $table->enum('type', ['media', 'signature']);
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
        Schema::dropIfExists('picture_extensions');
    }
}
