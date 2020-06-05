<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterCorporateIdentitiesTableAddImage extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('corporate_identities')) {
            Schema::table('corporate_identities', function(Blueprint $table) {
                $table->mediumText('image')->nullable();
            });
        }
    }
}
