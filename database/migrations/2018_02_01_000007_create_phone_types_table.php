<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePhoneTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('phone_types', function (Blueprint $table) {
            $table->string('id', 16)->primary();
        });
        DB::table('phone_types')->insert([
            ['id' => 'home'],
            ['id' => 'mobile'],
            ['id' => 'work'],
            ['id' => 'other'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('phone_types');
    }
}
