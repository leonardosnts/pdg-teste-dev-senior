<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salinity_surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marine_lab_id')->constrained('marine_labs');
            $table->string('transect');
            $table->decimal('surface_psu', 5, 2);
            $table->decimal('mid_psu', 5, 2);
            $table->decimal('deep_psu', 5, 2);
            $table->timestamp('surveyed_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salinity_surveys');
    }
};
