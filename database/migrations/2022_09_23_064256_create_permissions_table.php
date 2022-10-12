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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->boolean(BuyFormRgb);
            $table->boolean(SelleFeature);
            $table->boolean(Withdraw);
            $table->boolean(JoinUnion);
            $table->boolean(DynastyManagement);
            $table->boolean(ParticipateInUnionProjects);
            $table->boolean(ParticipateInChallenges);
            $table->boolean(ParticipateInContests);
            $table->boolean(EstablishStoreOrOffice);
            $table->boolean(ConstructionOfTheBuilding);

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
        Schema::dropIfExists('permissions');
    }
};
