#<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistsAprovingUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklists_approving_users', function (Blueprint $table) {
             $table->increments('id');
             $table->uuid('checklistId');
             $table->foreign('checklistId')
                ->references('id')
                ->on('checklists')
                ->onDelete('cascade');
             $table->uuid('userId');
             $table->foreign('userId')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
