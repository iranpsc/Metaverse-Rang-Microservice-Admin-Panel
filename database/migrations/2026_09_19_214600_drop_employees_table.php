<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('employees');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('fname');
            $table->string('lname');
            $table->string('melli_code');
            $table->date('birthdate');
            $table->string('hometown');
            $table->string('father_name');
            $table->enum('gender', ['male', 'female']);
            $table->enum('marriage_status', ['single', 'married']);
            $table->string('home_phone');
            $table->string('phone');
            $table->string('address');
            $table->integer('employee_code');
            $table->date('entry_date');
            $table->string('email');
            $table->timestamps();
        });
    }
};
