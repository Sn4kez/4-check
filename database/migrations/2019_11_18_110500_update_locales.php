<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateLocales extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_localeid_foreign');
            $table->foreign('localeId', 'users_localeid_foreign')
                ->references('id')
                ->on('locales')
                ->onUpdate('cascade');
        });
        foreach (['de-DE' => 'de', 'en-US' => 'en', 'fr-FR' => 'fr'] as $oldLocale => $newLocale) {
            DB::table('locales')
                ->where('id', '=', $oldLocale)
                ->update(['id' => $newLocale]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        foreach (['de' => 'de-DE', 'en' => 'en-US', 'fr' => 'fr-FR'] as $oldLocale => $newLocale) {
            DB::table('locales')
                ->where('id', '=', $oldLocale)
                ->update([
                    ['id' => $newLocale],
                ]);
            DB::table('users')
                ->where('localeId', '=', $oldLocale)
                ->update(['localeId' => $newLocale]);
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_localeid_foreign');
            $table->foreign('localeId', 'users_localeid_foreign')
                ->references('id')
                ->on('locales');
        });
    }
}
