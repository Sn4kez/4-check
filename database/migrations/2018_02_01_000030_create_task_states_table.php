<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Ramsey\Uuid\Uuid;
use App\TaskState;

class CreateTaskStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 16);
            $table->uuid('companyId')->nullable();
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamp('deletedAt')->nullable(true);
        });

        DB::table('task_states')->insert([
            ['id' => Uuid::uuid4()->toString(), 'name' => TaskState::NAME_TODO, 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => TaskState::NAME_DONE, 'companyId' => null]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task_states');
    }
}
