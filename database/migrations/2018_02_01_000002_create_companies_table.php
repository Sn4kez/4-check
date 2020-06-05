<?php

use App\Payment;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 128);
            $table->string('sectorId', 16)->nullable();
            $table->foreign('sectorId')->references('id')->on('sectors');
            $table->boolean('isActive')->default(1);
            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();

            foreach (Payment::getNewTableColumns() as $dbAttribute => $postKey) {
                $table->string($dbAttribute)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('companies');
    }
}
