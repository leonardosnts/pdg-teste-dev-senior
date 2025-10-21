<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expedition_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marine_lab_id')->constrained('marine_labs');
            $table->string('expedition_code')->unique();
            $table->string('region');
            $table->unsignedTinyInteger('anomaly_score')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('expedition_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expedition_report_id')->constrained('expedition_reports');
            $table->string('instrument');
            $table->text('summary');
            $table->unsignedInteger('sample_count');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedition_observations');
        Schema::dropIfExists('expedition_reports');
    }
};
