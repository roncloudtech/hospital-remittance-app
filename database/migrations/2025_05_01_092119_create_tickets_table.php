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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('subject');
            $table->text('message');
            $table->string('evidence_path')->nullable();
            $table->enum('status', ['open', 'closed', 'resolved'])->default('open');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Schema::create('ticket_replies', function (Blueprint $table) {
        //     $table->id();
        //     $table->unsignedBigInteger('ticket_id');
        //     $table->unsignedBigInteger('user_id'); // can be admin or remitter
        //     $table->text('message');
        //     $table->string('attachment')->nullable();
        //     $table->timestamps();

        //     $table->foreign('ticket_id')->references('id')->on('tickets')->onDelete('cascade');
        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('tickets');
        // Schema::dropIfExists('ticket_replies');
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Schema::dropIfExists('tickets');
        // Schema::dropIfExists('ticket_replies');
    }
};
