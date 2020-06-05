<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSectorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sectors', function (Blueprint $table) {
            $table->string('id', 32)->primary();
        });
        DB::table('sectors')->insert([
            ['id' => 'building'],
            ['id' => 'food'],
            ['id' => 'health'],
            ['id' => 'transport'],
            ['id' => 'industry'],
            ['id' => 'cleaning'],
            ['id' => 'catering'],
            ['id' => 'misc']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sectors');
    }
}
