<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feature_limits', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('start_id');
            $table->string('end_id')->nullable();
            $table->boolean('verified_kyc_limit')->default(false);
            $table->boolean('verified_bank_account_limit')->default(false);
            $table->boolean('not_sellable')->default(false);
            $table->boolean('under_18_limit')->default(false);
            $table->boolean('more_than_18_limit')->default(false);
            $table->boolean('dynasty_owner_limit')->default(false);
            $table->unsignedBigInteger('price')->default(0);
            $table->integer('individual_buy_limit')->default(0);
            $table->string('start_date');
            $table->string('end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feature_limits');
    }
};
