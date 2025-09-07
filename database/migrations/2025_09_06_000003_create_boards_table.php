<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('boards', function (Blueprint $table) {
      $table->id(); // BIGINT UNSIGNED AI

      // 投稿者
      $table->foreignId('author_id')
        ->constrained('users')           // users.id 既存テーブル
        ->cascadeOnUpdate()
        ->restrictOnDelete();            // ユーザー削除時に物理連鎖を避ける（boardsはSoftDeletes運用）

      // UI順の項目
      $table->foreignId('category_id')
        ->constrained('categories')
        ->cascadeOnUpdate()
        ->restrictOnDelete();

      $table->string('photo_path', 255)->nullable();
      $table->string('location_name', 100)->nullable();
      $table->decimal('location_lat', 10, 7)->nullable();
      $table->decimal('location_lng', 10, 7)->nullable();
      $table->string('description', 150);
      $table->string('link_url', 255)->nullable();

      // 内部管理
      $table->unsignedInteger('view_count')->default(0);
      $table->unsignedInteger('favorite_count')->default(0);

      $table->timestamps();
      $table->softDeletes(); // deleted_at
    });

    // インデックス: (category_id, created_at DESC) / (created_at DESC)
    // MySQL 8.0+ では降順インデックスを試み、失敗した場合は通常インデックスへフォールバック
    try {
      DB::statement('CREATE INDEX idx_boards_category_created_at_desc ON boards (category_id, created_at DESC)');
      DB::statement('CREATE INDEX idx_boards_created_at_desc ON boards (created_at DESC)');
    } catch (\Throwable $e) {
      Schema::table('boards', function (Blueprint $table) {
        $table->index(['category_id', 'created_at'], 'idx_boards_category_created_at');
        $table->index('created_at', 'idx_boards_created_at');
      });
    }
  }

  public function down(): void
  {
    // インデックス削除（存在しない場合に備えてtry-catch）
    try {
      DB::statement('DROP INDEX idx_boards_category_created_at_desc ON boards');
    } catch (\Throwable $e) {
    }
    try {
      DB::statement('DROP INDEX idx_boards_created_at_desc ON boards');
    } catch (\Throwable $e) {
    }

    Schema::table('boards', function (Blueprint $table) {
      try {
        $table->dropIndex('idx_boards_category_created_at');
      } catch (\Throwable $e) {
      }
      try {
        $table->dropIndex('idx_boards_created_at');
      } catch (\Throwable $e) {
      }
    });

    Schema::dropIfExists('boards');
  }
};
