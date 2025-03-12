<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // まず、既存の外部キー制約を削除
        Schema::table('photos', function (Blueprint $table) {
            $table->dropForeign(['spot_id']);
        });

        // 修正した外部キー制約を追加
        Schema::table('photos', function (Blueprint $table) {
            $table->foreign('spot_id')->references('id')->on('spots')->onDelete('cascade');
        });
    }

    public function down()
    {
        // 外部キー制約を削除する
        Schema::table('photos', function (Blueprint $table) {
            $table->dropForeign(['spot_id']);
        });
    }
};
