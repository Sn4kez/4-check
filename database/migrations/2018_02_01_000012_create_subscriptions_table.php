<?php

use App\CompanySubscription;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('company_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('companyId');

            $table->foreign('companyId')
                ->references('id')
                ->on('companies')
                ->onDelete('cascade');

            $table->boolean('viewCompanySubscription')->default(true);

            foreach (CompanySubscription::PROTECTED_MODELS as $model) {
                $name = substr(strrchr($model, '\\'), 1);
                $table->boolean('index' . $name)->default(true);
                $table->boolean('view' . $name)->default(true);
                $table->boolean('create' . $name)->default(true);
                $table->boolean('update' . $name)->default(true);
                $table->boolean('delete' . $name)->default(true);
            }

            $table->timestamp('createdAt')->nullable();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('company_subscriptions');
    }
}
