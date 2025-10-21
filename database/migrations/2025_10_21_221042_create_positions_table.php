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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('altitude', 8, 2)->nullable();
            $table->decimal('speed', 6, 2)->nullable();
            $table->integer('heading')->nullable(); // 0-360 degrees
            $table->integer('satellites')->nullable();
            $table->decimal('accuracy', 6, 2)->nullable();
            $table->decimal('odometer', 12, 2)->nullable();
            $table->integer('fuel_level')->nullable();
            $table->decimal('battery_level', 5, 2)->nullable();
            $table->boolean('ignition')->nullable();
            $table->string('address')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('device_time');
            $table->timestamps();
            
            // Indexes for better query performance
            $table->index(['device_id', 'device_time']);
            $table->index('device_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
