<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateApproversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('approvers', function (Blueprint $table) {
            $table->uuid('checklist_id');
            $table->foreign('checklist_id')
                ->references('id')
                ->on('checklists');
            $table->string('approver_type');
            $table->string('approver_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approvers');
    }
}
