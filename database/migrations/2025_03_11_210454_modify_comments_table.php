<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            $table->dropForeign(['ido', 'keido']);
            // 既存の ido と keido カラムを削除
            $table->dropColumn(['ido', 'keido']);

            // spot_id を外部キーとして追加
            $table->foreignId('spot_id')->constrained('spots')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('comments', function (Blueprint $table) {
            // 変更を元に戻す処理（必要に応じて）
            $table->dropForeign(['spot_id']);
            $table->dropColumn('spot_id');
            $table->string('ido');
            $table->string('keido');
        });
    }
};
