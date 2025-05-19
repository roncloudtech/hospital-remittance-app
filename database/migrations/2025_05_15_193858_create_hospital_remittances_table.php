<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('hospital_remittances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('hospital_id')->constrained()->onDelete('cascade');
        $table->year('year');
        $table->unsignedTinyInteger('month'); // 1 - 12
        $table->decimal('amount_paid', 12, 2)->default(0);
        $table->timestamps();

        $table->unique(['hospital_id', 'year', 'month']); // one record per month
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hospital_remittances');
    }
};
