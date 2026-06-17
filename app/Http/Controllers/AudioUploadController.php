<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessAudioUpload;
use App\Models\AudioFile;
use App\Models\AudioUpload;
use App\Services\Mp3Analyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AudioUploadController extends Controller
{
    private const MAX_SYNC_SIZE_BYTES = 25 * 1024 * 1024; // 25 MB
    private const MAX_ASYNC_SIZE_BYTES = 64 * 1024 * 1024; // 64 MB

    public function __invoke(Request $request, Mp3Analyzer $analyzer): JsonResponse
    {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimetypes:audio/mpeg,audio/mp3',
                'extensions:mp3',
                'max:65536', // 64 MB in KB
            ],
        ]);

        /** @var UploadedFile $file */
        $file = $validated['file'];
        $fileSize = $file->getSize() ?? 0;

        if ($fileSize > self::MAX_ASYNC_SIZE_BYTES) {
            return response()->json([
                'error' => 'File exceeds maximum allowed size of 64 MB.',
                'max_size_bytes' => self::MAX_ASYNC_SIZE_BYTES,
            ], 413);
        }

        $sanitizedName = $this->sanitizeFilename($file->getClientOriginalName());

        if ($fileSize > self::MAX_SYNC_SIZE_BYTES) {
            return $this->handleLargeUpload($file, $sanitizedName);
        }

        return $this->handleSyncUpload($file, $sanitizedName, $analyzer);
    }

    private function handleSyncUpload(UploadedFile $file, string $sanitizedName, Mp3Analyzer $analyzer): JsonResponse
    {
        $realPath = $file->getRealPath();
        if ($realPath === false || ! file_exists($realPath)) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file could not be read.',
            ]);
        }

        $hash = hash_file('sha256', $realPath);
        $original = AudioUpload::query()
            ->where('content_hash', $hash)
            ->whereNull('duplicate_of_id')
            ->oldest('id')
            ->first();

        $path = $file->store('audio_uploads');

        if ($path === false) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file could not be stored.',
            ]);
        }

        $analysis = $analyzer->analyze(Storage::path($path));

        $upload = AudioUpload::query()->create([
            'original_filename' => $sanitizedName,
            'storage_path' => $path,
            'content_hash' => $hash,
            'file_size_bytes' => $file->getSize() ?? 0,
            'duration_seconds' => $analysis->durationSeconds,
            'bitrate_kbps' => $analysis->bitrateKbps,
            'sample_rate_hz' => $analysis->sampleRateHz,
            'quality_score' => $analysis->qualityScore,
            'is_duration_outlier' => $analysis->isDurationOutlier,
            'duplicate_of_id' => $original?->id,
        ]);

        // Also create linked AudioFile for admin panel visibility
        AudioFile::query()->create([
            'title' => $sanitizedName,
            'filename' => $sanitizedName,
            'file_path' => $path,
            'size' => $file->getSize() ?? 0,
            'duration' => $analysis->durationSeconds,
            'audio_upload_id' => $upload->id,
            'content_hash' => $hash,
            'bitrate_kbps' => $analysis->bitrateKbps,
            'sample_rate_hz' => $analysis->sampleRateHz,
            'quality_score' => $analysis->qualityScore,
            'is_duration_outlier' => $analysis->isDurationOutlier,
        ]);

        return response()->json([
            'id' => $upload->id,
            'duplicate' => [
                'is_duplicate' => $original !== null,
                'original_upload_id' => $original?->id,
            ],
            'analysis' => [
                'duration_seconds' => $analysis->durationSeconds,
                'duration' => $analysis->durationSeconds === null ? null : $this->formatDuration($analysis->durationSeconds),
                'is_duration_outlier' => $analysis->isDurationOutlier,
                'quality_score' => $analysis->qualityScore,
                'bitrate_kbps' => $analysis->bitrateKbps,
                'sample_rate_hz' => $analysis->sampleRateHz,
                'file_size_bytes' => $upload->file_size_bytes,
            ],
        ], 201);
    }

    private function handleLargeUpload(UploadedFile $file, string $sanitizedName): JsonResponse
    {
        $tempPath = sys_get_temp_dir() . '/audio_upload_' . uniqid('', true) . '.mp3';
        $file->move(dirname($tempPath), basename($tempPath));

        ProcessAudioUpload::dispatch(
            $tempPath,
            $sanitizedName,
            null,
            $file->getSize() ?? 0,
        );

        return response()->json([
            'message' => 'File is large and is being processed in the background.',
            'notification' => 'You will be notified when processing is complete.',
            'file_size_bytes' => $file->getSize() ?? 0,
            'max_sync_size_bytes' => self::MAX_SYNC_SIZE_BYTES,
        ], 202);
    }

    private function sanitizeFilename(string $filename): string
    {
        $name = preg_replace('/[^\w\-. ]/u', '_', $filename);
        $name = preg_replace('/_{2,}/', '_', $name);
        $name = substr($name, 0, 255);

        return $name ?: 'unnamed_file.mp3';
    }

    private function formatDuration(float $seconds): string
    {
        $totalSeconds = (int) round($seconds);

        return sprintf('%02d:%02d', intdiv($totalSeconds, 60), $totalSeconds % 60);
    }
}
