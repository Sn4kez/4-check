<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Ramsey\Uuid\Uuid;

class CreateTaskTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_types', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 16);
            $table->uuid('companyId')->nullable();
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamp('deletedAt')->nullable(true);
        });

        DB::table('task_types')->insert([
            ['id' => Uuid::uuid4()->toString(), 'name' => 'offer', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'call', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'disinfection', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'e-mail', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'removal', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'inspection', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'overhauling', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'revision', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'reworking', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'cleaning', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'repairing', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'instruction', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'miscellaneous', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'maintenance', 'companyId' => null]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_types');
    }
}
