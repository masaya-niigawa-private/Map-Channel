<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            // users.id を参照（後で認証を載せるため一旦 nullable）
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title', 160);
            $table->mediumText('body');
            $table->boolean('is_resolved')->default(false);

            // 集計キャッシュ
            $table->unsignedInteger('answers_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('bookmarks_count')->default(0);

            $table->enum('status', ['published', 'hidden', 'draft'])->default('published');

            // 循環参照になるため、ここでは列とindexのみ作成（FKは後続#3で付与）
            $table->unsignedBigInteger('best_answer_id')->nullable()->index();

            $table->timestamps();         // TIMESTAMP（envのcharset/collationは接続設定に従う）
            $table->softDeletes();        // deleted_at

            // 検索（MariaDB FULLTEXT対応）
            $table->fullText(['title', 'body'], 'ftx_questions_title_body');
            $table->index(['author_id'], 'idx_questions_author');
            $table->index(['status'], 'idx_questions_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
