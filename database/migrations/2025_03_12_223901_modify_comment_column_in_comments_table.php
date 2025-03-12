<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('comments', function (Blueprint $table) {
            // 'comment' カラムをNULL許容に変更
            $table->text('comment')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('comments', function (Blueprint $table) {
            // 元に戻す（NULL非許容にする）
            $table->text('comment')->nullable(false)->change();
        });
    }
};
