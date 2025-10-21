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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('imei')->unique()->nullable();
            $table->enum('type', ['car', 'truck', 'van', 'motorcycle', 'equipment', 'other'])->default('car');
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->string('plate_number')->nullable();
            $table->string('model')->nullable();
            $table->string('color')->nullable();
            $table->year('year')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_phone')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
