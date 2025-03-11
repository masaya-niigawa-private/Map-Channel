<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('photos', function (Blueprint $table) {
            // `photos` テーブルの主キー（AUTO_INCREMENT）
            $table->id(); 
            //spotsテーブルの主キー
            $table->foreignId('spot_id')->constrained('spots')->onDelet('cascade');
            $table->string('photo_path')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('photos');
    }
};
