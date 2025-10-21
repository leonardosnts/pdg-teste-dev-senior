<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alert_channels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marine_lab_id')->constrained('marine_labs');
            $table->string('channel');
            $table->string('endpoint');
            $table->json('constraints')->nullable();
            $table->timestamps();
        });

        Schema::create('alert_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marine_lab_id')->constrained('marine_labs');
            $table->string('event_type');
            $table->json('payload');
            $table->timestamp('triggered_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alert_events');
        Schema::dropIfExists('alert_channels');
    }
};
