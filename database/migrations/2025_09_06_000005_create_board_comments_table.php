<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('board_comments', function (Blueprint $table) {
      $table->id();

      $table->foreignId('board_id')
        ->constrained('boards')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();

      $table->foreignId('author_id')
        ->constrained('users')
        ->cascadeOnUpdate()
        ->restrictOnDelete();

      // 設計書準拠: VARCHAR(500)
      $table->string('content', 500);

      $table->timestamps();
      $table->softDeletes(); // deleted_at

      // インデックス
      $table->index(['board_id', 'created_at'], 'idx_board_comments_board_created');
      $table->index('author_id', 'idx_board_comments_author');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('board_comments');
  }
};
