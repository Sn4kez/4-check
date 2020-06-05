<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->boolean('showCompanyName')->default(true);
            $table->boolean('showCompanyAddress')->default(true);
            $table->boolean('showUsername')->default(true);
            $table->boolean('showPageNumbers')->default(true);
            $table->boolean('showExportDate')->default(true);
            $table->boolean('showVersion')->default(true);
            $table->text('text')->nullable();
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
        Schema::dropIfExists('report_settings');
    }
}
