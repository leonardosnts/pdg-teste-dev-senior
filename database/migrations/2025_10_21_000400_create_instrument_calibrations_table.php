<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instrument_calibrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marine_lab_id')->constrained('marine_labs');
            $table->string('instrument');
            $table->decimal('drift_ppm', 8, 3);
            $table->timestamp('validated_at');
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instrument_calibrations');
    }
};
