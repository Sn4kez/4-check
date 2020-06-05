<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Ramsey\Uuid\Uuid;

class CreateLocationTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('location_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 255);
            $table->uuid('companyId')->nullable();
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamp('deletedAt')->nullable(true);
        });

        DB::table('location_types')->insert([
            ['id' => Uuid::uuid4()->toString(), 'name' => 'building', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'room', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'floor', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'area', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'machine', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'customer', 'companyId' => null]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('location_types');
    }
}
