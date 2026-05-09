<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('variable_change_logs', function (Blueprint $table) {
            $table->dropColumn(['variable_id', 'option_id']);
            $table->renameColumn('previous_price', 'previous_value');
            $table->renameColumn('current_price', 'current_value');
            $table->morphs('changeable');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
};
