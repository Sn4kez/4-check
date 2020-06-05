<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGroupsTableAddImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('groups')) {
            Schema::table('groups', function(Blueprint $table) {
                $table->mediumText('image')->nullable();
            });
        }
    }
}
