<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterLocationsTableAddParentId extends Migration
{
    /**
     * Run the migrations.
     * - Add a new column parentId so we can set a parent location for another location.
     * -- can go as deep as possible, there is no limit
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('locations')) {
            Schema::table('locations', function (Blueprint $table) {
                /**
                 * Add a nullable column parentId.
                 * It CAN be null if it has NO PARENT.
                 */
                $table->uuid('parentId')->nullable();

                /**
                 * Set parentId as referenced foreign key from other locations
                 * So, if someone would like to enter something here it MUST be
                 * another location
                 */
                $table->foreign('parentId')
                    ->references('id')
                    ->nullable()
                    ->on('locations');
            });

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /**
         * Nothing to do here.
         * The table will be deleted before in the create_locations_table migration
         */
    }
}
