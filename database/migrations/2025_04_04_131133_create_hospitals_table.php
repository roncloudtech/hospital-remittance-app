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
            $table->string('hospital_id')->unique(); // Format: XXX-123456
            $table->string('hospital_name');
            $table->string('hospital_formation');
            $table->string('address');
            $table->string('phone_number')->unique();
            $table->foreignId('hospital_remitter')->constrained('users')->onDelete('cascade'); // Foreign key to users table
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
