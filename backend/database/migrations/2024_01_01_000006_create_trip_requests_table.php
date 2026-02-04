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
        Schema::create('trip_requests', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->foreignId('traveler_id')->constrained()->onDelete('cascade');
            $table->foreignId('destination_id')->constrained()->onDelete('cascade');
            $table->dateTime('departure_datetime');
            $table->dateTime('return_datetime');
            $table->enum('status', ['requested', 'approved', 'cancelled'])->default('requested');
            $table->timestamps();

            // Indexes for better performance
            $table->index('traveler_id');
            $table->index('destination_id');
            $table->index('status');
            $table->index('departure_datetime');
            $table->index('return_datetime');
            $table->index(['traveler_id', 'status']);
            $table->index(['destination_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trip_requests');
    }
};
