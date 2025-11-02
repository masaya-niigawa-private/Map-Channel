<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_bookmarks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('created_at')->nullable();

            $table->unique(['question_id', 'user_id'], 'uq_qbm');
            $table->index(['user_id', 'created_at'], 'idx_qbm_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_bookmarks');
    }
};
