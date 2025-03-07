<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('spots', function (Blueprint $table) {
            // ido, keido の組み合わせにユニーク制約を追加
            $table->unique(['ido', 'keido']);
        });
    }

    public function down(): void
    {
        Schema::table('spots', function (Blueprint $table) {
            // ロールバック時にユニークキーを削除
            $table->dropUnique(['ido', 'keido']);
        });
    }
};
