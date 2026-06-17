<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audio_files', function (Blueprint $table): void {
            $table->foreignId('audio_upload_id')->nullable()->after('id')->constrained('audio_uploads')->nullOnDelete();
            $table->string('content_hash', 64)->nullable()->after('duration');
            $table->unsignedSmallInteger('bitrate_kbps')->nullable()->after('content_hash');
            $table->unsignedMediumInteger('sample_rate_hz')->nullable()->after('bitrate_kbps');
            $table->unsignedTinyInteger('quality_score')->nullable()->after('sample_rate_hz');
            $table->boolean('is_duration_outlier')->default(false)->after('quality_score');
        });
    }

    public function down(): void
    {
        Schema::table('audio_files', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('audio_upload_id');
            $table->dropColumn(['content_hash', 'bitrate_kbps', 'sample_rate_hz', 'quality_score', 'is_duration_outlier']);
        });
    }
};
