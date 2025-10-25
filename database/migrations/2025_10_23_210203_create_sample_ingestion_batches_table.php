<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_ingestion_batches', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('marine_lab_id')->constrained();
            $table->integer('total_batches');
            $table->integer('processed_batches')->default(0);
            $table->integer('total_iterations');
            $table->json('parameters');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['uuid', 'marine_lab_id']);
            $table->index('completed_at');
            $table->index('failed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_ingestion_batches');
    }
};
