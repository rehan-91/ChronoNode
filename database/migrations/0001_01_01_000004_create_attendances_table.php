<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->timestamp('check_in')->nullable();
            $table->timestamp('check_out')->nullable();
            $table->string('status')->default('absent');
            
            // Metrics (calculated server-side)
            $table->integer('working_minutes')->default(0);
            $table->integer('late_minutes')->default(0);
            $table->integer('early_departure_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            
            // GPS Tracking Context
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('distance_meters')->nullable();
            $table->integer('gps_accuracy')->nullable();
            $table->boolean('is_automatic_checkout')->default(false);
            
            $table->timestamps();

            // CRITICAL: Double check-in concurrency protection
            $table->unique(['user_id', 'date']);
            
            // Lookup optimizations
            $table->index(['date', 'status']);
            $table->index(['office_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
