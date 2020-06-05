<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Ramsey\Uuid\Uuid;

class CreateTaskPrioritiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_priorities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 16);
            $table->uuid('companyId')->nullable();
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamp('deletedAt')->nullable(true);
        });

        DB::table('task_priorities')->insert([
            ['id' => Uuid::uuid4()->toString(), 'name' => 'low', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'medium', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'high', 'companyId' => null]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_priorities');
    }
}
