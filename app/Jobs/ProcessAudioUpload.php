<?php

namespace App\Jobs;

use App\Models\AudioFile;
use App\Models\AudioUpload;
use App\Services\Mp3Analyzer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessAudioUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    public function __construct(
        private readonly string $tempPath,
        private readonly string $originalFilename,
        private readonly ?string $title,
        private readonly int $fileSize,
    ) {}

    public function handle(Mp3Analyzer $analyzer): void
    {
        if (! file_exists($this->tempPath)) {
            Log::error('ProcessAudioUpload: temp file missing', ['path' => $this->tempPath]);
            return;
        }

        $hash = hash_file('sha256', $this->tempPath);

        $original = AudioUpload::query()
            ->where('content_hash', $hash)
            ->whereNull('duplicate_of_id')
            ->oldest('id')
            ->first();

        $path = 'audio/' . basename($this->tempPath);
        Storage::disk('public')->put($path, file_get_contents($this->tempPath));

        $analysis = $analyzer->analyze(Storage::disk('public')->path($path));

        $upload = AudioUpload::query()->create([
            'original_filename' => $this->originalFilename,
            'storage_path' => $path,
            'content_hash' => $hash,
            'file_size_bytes' => $this->fileSize,
            'duration_seconds' => $analysis->durationSeconds,
            'bitrate_kbps' => $analysis->bitrateKbps,
            'sample_rate_hz' => $analysis->sampleRateHz,
            'quality_score' => $analysis->qualityScore,
            'is_duration_outlier' => $analysis->isDurationOutlier,
            'duplicate_of_id' => $original?->id,
        ]);

        AudioFile::query()->create([
            'title' => $this->title ?? $this->originalFilename,
            'filename' => $this->originalFilename,
            'file_path' => $path,
            'size' => $this->fileSize,
            'duration' => $analysis->durationSeconds,
            'audio_upload_id' => $upload->id,
            'content_hash' => $hash,
            'bitrate_kbps' => $analysis->bitrateKbps,
            'sample_rate_hz' => $analysis->sampleRateHz,
            'quality_score' => $analysis->qualityScore,
            'is_duration_outlier' => $analysis->isDurationOutlier,
        ]);

        @unlink($this->tempPath);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessAudioUpload failed', [
            'path' => $this->tempPath,
            'error' => $exception->getMessage(),
        ]);

        if (file_exists($this->tempPath)) {
            @unlink($this->tempPath);
        }
    }
}
