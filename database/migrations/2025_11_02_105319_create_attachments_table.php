<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');  // attachable_type (string), attachable_id (UBIGINT), index付与
            $table->string('url', 512);
            $table->string('mime_type', 100);
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index(['attachable_type', 'attachable_id'], 'idx_att_poly');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
