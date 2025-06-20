<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('hospital_remittances', function (Blueprint $table) {
            $table->decimal('monthly_target', 15, 2)->nullable()->after('hospital_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hospital_remittances', function (Blueprint $table) {
            $table->dropColumn('monthly_target');
        });
    }
};
