<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('spots', function (Blueprint $table) {
            $table->id();
            $table->String('ido');
            $table->String('keido');
            $table->String('spot_name');
            $table->String('photo_path')->nullable(); //画像のパス（外部ストレージに保存）
            $table->String('evaluation'); //5段階評価
            $table->String('user_name')->nullable(); //登録ユーザー
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spots');
    }
};
