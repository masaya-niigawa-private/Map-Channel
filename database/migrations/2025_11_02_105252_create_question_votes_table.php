<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('question_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('value');   // +1 / -1
            $table->timestamp('created_at')->nullable(); // 単独で十分

            $table->unique(['question_id', 'user_id'], 'uq_qvote');
            $table->index(['user_id'], 'idx_qvotes_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_votes');
    }
};
