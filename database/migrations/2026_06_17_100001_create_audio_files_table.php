<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audio_files', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('filename');
            $table->string('file_path');
            $table->unsignedBigInteger('size');
            $table->decimal('duration', 10, 3)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audio_files');
    }
};
