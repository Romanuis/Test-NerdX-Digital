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
        Schema::create('content_generations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Content type and status
            $table->string('type'); // article, rewrite, summary, email, translation
            $table->string('status')->default('pending'); // pending, processing, completed, failed

            // Input data
            $table->text('input_text')->nullable();
            $table->json('input_parameters')->nullable(); // tone, language, length, etc.

            // Output data
            $table->longText('output_text')->nullable();
            $table->json('metadata')->nullable(); // tokens used, model, etc.

            // Error handling
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);

            // Credits
            $table->integer('credits_used')->default(0);

            // Timestamps
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_generations');
    }
};
