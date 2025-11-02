<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('impressions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->char('session_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            // 生成列（MariaDB 11 で PERSISTENT/STORED 相当）
            $table->date('view_date')->storedAs("DATE(`created_at`)");

            $table->unique(['question_id', 'user_id', 'session_hash', 'view_date'], 'uq_imp_daily');
            $table->index(['question_id', 'created_at'], 'idx_imp_q');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impressions');
    }
};
