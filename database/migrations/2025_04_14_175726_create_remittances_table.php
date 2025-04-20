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
        Schema::create('remittances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->constrained()->onDelete('cascade');
            $table->foreignId('remitter_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->string('payment_reference')->unique();
            $table->string('payment_status')->default('pending');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('payment_method');
            $table->softDeletes();
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remittances');
    }
};
