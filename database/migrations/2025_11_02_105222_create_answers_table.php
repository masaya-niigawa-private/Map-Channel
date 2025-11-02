<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->mediumText('body');
            $table->boolean('is_best')->default(false);
            $table->unsignedInteger('upvotes_count')->default(0);
            $table->enum('status', ['published', 'hidden'])->default('published');

            $table->timestamps();
            $table->softDeletes();

            $table->fullText(['body'], 'ftx_answers_body');
            $table->index(['question_id', 'is_best'], 'idx_answers_question_best');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
