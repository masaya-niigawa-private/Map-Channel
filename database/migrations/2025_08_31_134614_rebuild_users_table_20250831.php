<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->string('uid', 128)->primary(); // 文字列主キー（桁数を明示）
            $table->string('userName')->nullable(); // ← NULL許可に変更
            $table->string('email')->unique();      // 一意メール
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        // Laravel標準 users に近い形へ戻す（必要なら）
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();            // ← unique に戻す
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
};
