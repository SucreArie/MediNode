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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();

            $table->string('firstName');
            $table->string('lastName');
            $table->string('email')->unique()->nullable();
            $table->string('phone')->nullable();

            $table->date('birthDate')->nullable();
            $table->string('gender')->nullable();

            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postalCode')->nullable();

            $table->string('bloodType')->nullable();
            $table->text('allergies')->nullable();

            $table->string('emergencyName')->nullable();
            $table->string('emergencyPhone')->nullable();

            $table->string('insuranceId')->nullable();

            $table->text('condition')->nullable();
            $table->text('notes')->nullable();

            $table->string('status')->default('stable');

            $table->foreignId('centre_medical_id')->constrained('centre_medicauxes')->onDelete('cascade');


            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
