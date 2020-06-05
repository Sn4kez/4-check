<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Ramsey\Uuid\Uuid;

class CreateAuditStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('audit_states', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 30);
            $table->uuid('companyId')->nullable();
            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');
            $table->timestamp('deletedAt')->nullable(true);
        });

        DB::table('audit_states')->insert([
            ['id' => Uuid::uuid4()->toString(), 'name' => 'finished', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'draft', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'approved', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'awaiting approval', 'companyId' => null],
            ['id' => Uuid::uuid4()->toString(), 'name' => 'sync needed', 'companyId' => null],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('audit_states');
    }
}
