<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::create('board_reports', function (Blueprint $table) {
      $table->id();

      $table->foreignId('board_id')
        ->constrained('boards')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();

      $table->foreignId('reporter_id')
        ->constrained('users')
        ->cascadeOnUpdate()
        ->restrictOnDelete();

      $table->string('reason', 255)->nullable();

      $table->timestamps();

      // インデックス
      $table->index(['board_id', 'created_at'], 'idx_board_reports_board_created');
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('board_reports');
  }
};
