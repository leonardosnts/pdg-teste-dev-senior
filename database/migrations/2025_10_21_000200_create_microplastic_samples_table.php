<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('microplastic_samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marine_lab_id')->constrained('marine_labs');
            $table->uuid('batch');
            $table->unsignedInteger('particle_count');
            $table->unsignedSmallInteger('depth_meters');
            $table->decimal('temperature_celsius', 5, 2);
            $table->timestamp('collected_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('microplastic_samples');
    }
};
