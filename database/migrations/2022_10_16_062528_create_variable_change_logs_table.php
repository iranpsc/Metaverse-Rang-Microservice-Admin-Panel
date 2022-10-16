<?php

use App\Models\Option;
use App\Models\Variable;
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
        Schema::create('variable_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Variable::class)->nullable();
            $table->foreignIdFor(Option::class)->nullable();
            $table->string('changer_name');
            $table->integer('previous_price');
            $table->integer('current_price');
            $table->text('note')->nullable();
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
        Schema::dropIfExists('variable_change_logs');
    }
};
