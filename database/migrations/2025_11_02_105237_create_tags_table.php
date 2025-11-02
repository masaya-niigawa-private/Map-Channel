<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name', 48);
            $table->unsignedInteger('questions_count')->default(0);
            $table->timestamps();

            $table->unique(['name'], 'uq_tags_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
