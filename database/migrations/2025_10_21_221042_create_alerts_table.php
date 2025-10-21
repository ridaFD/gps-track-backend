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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->foreignId('geofence_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', [
                'geofence_entry',
                'geofence_exit',
                'speed_limit',
                'idle',
                'low_battery',
                'sos',
                'power_cut',
                'movement',
                'other'
            ]);
            $table->enum('severity', ['info', 'warning', 'high', 'critical'])->default('info');
            $table->string('message');
            $table->json('data')->nullable();
            $table->boolean('read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['device_id', 'created_at']);
            $table->index(['read', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
