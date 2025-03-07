<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->String('ido');
            $table->String('keido');
            $table->text('comment');
            $table->timestamps();

            // 外部キー制約を追加
            $table->foreign(['ido', 'keido'])
                ->references(['ido', 'keido'])
                ->on('spots')
                ->onDelete('cascade'); // 親のスポットが削除されたらコメントも削除
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
