<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();

            // 多態ターゲット（質問 or 回答）
            $table->string('target_type', 32);   // 'question' | 'answer'
            $table->unsignedBigInteger('target_id');

            $table->string('reason_code', 40);
            $table->string('note', 500)->nullable();
            $table->enum('status', ['open', 'reviewing', 'closed'])->default('open');

            $table->timestamps();

            $table->index(['target_type', 'target_id'], 'idx_reports_target');
            $table->index(['status'], 'idx_reports_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
