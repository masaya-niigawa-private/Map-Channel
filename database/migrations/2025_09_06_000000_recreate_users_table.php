<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    // 既存を削除して作り直し（データは消えます）
    Schema::dropIfExists('users');

    Schema::create('users', function (Blueprint $table) {
      $table->id();                               // BIGINT UNSIGNED AI (PK)
      $table->string('uid', 36)->unique();        // Firebase UID（設計：36文字）
      $table->string('userName', 100)->nullable();// ユーザー名（設計：100文字）
      $table->string('email', 255)->unique();     // メール（UNIQUE）
      $table->timestamps();                       // created_at / updated_at
    });
  }

  public function down(): void
  {
    Schema::dropIfExists('users');
  }
};
