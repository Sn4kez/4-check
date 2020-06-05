<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationsTable extends Migration
{

    const TABLE_NAME_USERS = 'users';
    const COLUMN_NAME_USER = 'user_id';
    const COLUMN_NAME_SENDER = 'sender_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            /**
             * 'user_id' is a UUID and references to the 'id' in the 'users' table
             */
            $table->uuid(self::COLUMN_NAME_USER);
            $table->foreign(self::COLUMN_NAME_USER)
                ->references('id')
                ->nullable()
                ->on(self::TABLE_NAME_USERS);

            /**
             * 'sender_id' is a UUID and references to the id in the 'users' table - just like the 'user_id'
             */
            $table->uuid(self::COLUMN_NAME_SENDER);
            $table->foreign(self::COLUMN_NAME_SENDER)
                ->references('id')
                ->on(self::TABLE_NAME_USERS);

            /**
             * Further non complex values
             */
            $table->string('link', 255);
            $table->string('title', 255);
            $table->string('message', 10000);
            $table->tinyInteger('read');
            $table->tinyInteger('pushed');

            /**
             * Timestamps
             */
            $table->timestamp('createdAt');
            $table->timestamp('readAt');
            $table->timestamp('updatedAt');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}
