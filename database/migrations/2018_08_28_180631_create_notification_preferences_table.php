<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotificationPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('userId');
            $table->foreign('userId')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
            $table->boolean('checklistNeedsActivityNotification')->default(1);
            $table->boolean('checklistCompletedNotification')->default(1);
            $table->boolean('checklistDueNotification')->default(1);
            $table->boolean('checklistCriticalRatingNotification')->default(1);
            $table->boolean('checklistAssignedNotification')->default(1);

            $table->boolean('taskCompletedNotification')->default(1);
            $table->boolean('taskAssignedNotification')->default(1);
            $table->boolean('taskOverdueNotification')->default(1);
            $table->boolean('taskUpdatedNotification')->default(1);
            $table->boolean('taskDeletedNotification')->default(1);

            $table->boolean('checklistNeedsActivityMail')->default(1);
            $table->boolean('checklistCompletedMail')->default(1);
            $table->boolean('checklistDueMail')->default(1);
            $table->boolean('checklistCriticalRatingMail')->default(1);
            $table->boolean('checklistAssignedMail')->default(1);

            $table->boolean('taskCompletedMail')->default(1);
            $table->boolean('taskAssignedMail')->default(1);
            $table->boolean('taskOverdueMail')->default(1);
            $table->boolean('taskUpdatedMail')->default(1);
            $table->boolean('taskDeletedMail')->default(1);

            $table->boolean('auditAssignedNotification')->default(1);
            $table->boolean('auditCompletedNotification')->default(1);
            $table->boolean('auditOverdueNotification')->default(1);
            $table->boolean('auditReleaseRequiredNotification')->default(1);

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
        Schema::dropIfExists('notification_preferences');
    }
}
