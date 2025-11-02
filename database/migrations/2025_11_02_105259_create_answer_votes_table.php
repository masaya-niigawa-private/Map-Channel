<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('answer_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('answer_id')->constrained('answers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->tinyInteger('value');   // +1 を推奨
            $table->timestamp('created_at')->nullable();

            $table->unique(['answer_id', 'user_id'], 'uq_avote');
            $table->index(['user_id'], 'idx_avotes_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('answer_votes');
    }
};
