<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spot_id')
                ->constrained('spots')
                ->onDelete('cascade'); // spotが削除されたら関連するpostも削除
            $table->string('author'); // 投稿者
            $table->text('content');  // 投稿内容
            $table->timestamps();     // 投稿時間（created_at, updated_at）
        });
    }

    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
