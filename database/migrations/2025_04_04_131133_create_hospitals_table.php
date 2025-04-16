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
        Schema::create('hospitals', function (Blueprint $table) {
            $table->id();
            $table->string('hospital_id', 10)->unique(); // Format: XXX-123456 (3 letters, 6 numbers)
            $table->string('hospital_name');
            $table->string('military_division'); // Renamed for clarity (stores military division)
            $table->string('address');
            $table->string('phone_number')->unique();
            $table->foreignId('hospital_remitter')->constrained('users')->onDelete('cascade');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospitals');
    }
};
