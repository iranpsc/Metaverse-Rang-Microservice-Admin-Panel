<?php

use App\Models\Kyc;
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
        Schema::create('kyc_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Kyc::class);
            $table->string('fname_err')->nullable();
            $table->string('lname_err')->nullable();
            $table->string('father_name_err')->nullable();
            $table->string('melli_code_err')->nullable();
            $table->string('province_err')->nullable();
            $table->string('city_err')->nullable();
            $table->string('street_err')->nullable();
            $table->string('number_err')->nullable();
            $table->string('postal_code_err')->nullable();
            $table->string('address_err')->nullable();
            $table->string('melli_card_err')->nullable();
            $table->string('prove_picture_err')->nullable();
            $table->string('resume_err')->nullable();
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
        Schema::dropIfExists('kyc_errors');
    }
};
