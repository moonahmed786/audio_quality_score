<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audio_uploads', function (Blueprint $table): void {
            $table->id();
            $table->string('original_filename');
            $table->string('storage_path');
            $table->char('content_hash', 64)->index();
            $table->unsignedBigInteger('file_size_bytes');
            $table->decimal('duration_seconds', 10, 3)->nullable();
            $table->unsignedInteger('bitrate_kbps')->nullable();
            $table->unsignedInteger('sample_rate_hz')->nullable();
            $table->unsignedTinyInteger('quality_score');
            $table->boolean('is_duration_outlier');
            $table->foreignId('duplicate_of_id')->nullable()->constrained('audio_uploads')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audio_uploads');
    }
};
