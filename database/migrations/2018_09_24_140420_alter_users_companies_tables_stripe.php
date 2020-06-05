<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterUsersCompaniesTablesStripe extends Migration
{
    /**
     * Run the migrations.
     * https://laravel.com/docs/5.7/billing#configuration
     *
     * @return void
     */
    public function up() {
        Schema::table('companies', function ($table) {
            $table->string('stripe_id')->nullable();
            $table->string('card_brand')->nullable();
            $table->string('card_last_four')->nullable();
            $table->timestamp('trial_ends_at')->nullable();

            $table->string('quantity')->nullable();
            $table->string('current_payment_method')->nullable();
            $table->string('current_package')->nullable();
        });

        Schema::create('subscriptions', function ($table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('name');
            $table->string('stripe_id');
            $table->string('stripe_plan');
            $table->integer('quantity');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
