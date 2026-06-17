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
            'title' => 'nullable|string|max:255',
        ]);

        /** @var UploadedFile $file */
        $file = $validated['file'];
        $fileSize = $this->getUploadedFileSize($file);

        if ($fileSize > self::MAX_ASYNC_SIZE_BYTES) {
            return response()->json([
                'error' => 'File exceeds maximum allowed size of 64 MB.',
                'max_size_bytes' => self::MAX_ASYNC_SIZE_BYTES,
            ], 413);
        }

        $originalName = $validated['title'] ?? $file->getClientOriginalName();
        $sanitizedName = $this->sanitizeFilename($originalName);

        if ($fileSize > self::MAX_SYNC_SIZE_BYTES) {
            return $this->handleLargeUpload($file, $sanitizedName, $fileSize);
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
        $fileSize = $this->getUploadedFileSize($file);
        $original = AudioUpload::query()
            ->where('content_hash', $hash)
            ->whereNull('duplicate_of_id')
            ->oldest('id')
            ->first();

        if ($original) {
            // Duplicate found, reference the original upload without storing a new file.
            $upload = AudioUpload::query()->create([
                'original_filename' => $sanitizedName,
                'storage_path' => $original->storage_path,
                'content_hash' => $hash,
                'file_size_bytes' => $fileSize,
                // No analysis for duplicate; reuse original values where appropriate.
                'duration_seconds' => $original->duration_seconds,
                'bitrate_kbps' => $original->bitrate_kbps,
                'sample_rate_hz' => $original->sample_rate_hz,
                'quality_score' => $original->quality_score,
                'is_duration_outlier' => $original->is_duration_outlier,
                'duplicate_of_id' => $original->id,
                'status' => 'completed',
            ]);

            // Optionally create a lightweight AudioFile entry referencing the original.
            AudioFile::query()->create([
                'title' => $sanitizedName,
                'filename' => $sanitizedName,
                'file_path' => $original->storage_path,
                'size' => $fileSize,
                'duration' => $original->duration_seconds,
                'audio_upload_id' => $upload->id,
                'content_hash' => $hash,
                'bitrate_kbps' => $original->bitrate_kbps,
                'sample_rate_hz' => $original->sample_rate_hz,
                'quality_score' => $original->quality_score,
                'is_duration_outlier' => $original->is_duration_outlier,
            ]);

            return response()->json([
                'id' => $upload->id,
                'duplicate' => [
                    'is_duplicate' => true,
                    'original_upload_id' => $original->id,
                ],
                'analysis' => [
                    'duration_seconds' => $original->duration_seconds,
                    'duration' => $this->formatDuration($original->duration_seconds),
                    'is_duration_outlier' => $original->is_duration_outlier,
                    'quality_score' => $original->quality_score,
                    'bitrate_kbps' => $original->bitrate_kbps,
                    'sample_rate_hz' => $original->sample_rate_hz,
                    'file_size_bytes' => $fileSize,
                ],
            ], 200);
        }

        // Not a duplicate – proceed with normal upload flow.
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
            'file_size_bytes' => $fileSize,
            'duration_seconds' => $analysis->durationSeconds,
            'bitrate_kbps' => $analysis->bitrateKbps,
            'sample_rate_hz' => $analysis->sampleRateHz,
            'quality_score' => $analysis->qualityScore,
            'is_duration_outlier' => $analysis->isDurationOutlier,
            'duplicate_of_id' => null,
            'status' => 'completed',
        ]);

        // Also create linked AudioFile for admin panel visibility
        AudioFile::query()->create([
            'title' => $sanitizedName,
            'filename' => $sanitizedName,
            'file_path' => $path,
            'size' => $fileSize,
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
                'is_duplicate' => false,
                'original_upload_id' => null,
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

    private function handleLargeUpload(UploadedFile $file, string $sanitizedName, int $fileSize): JsonResponse
    {
        // Move uploaded file to a temporary location.
        $tempPath = sys_get_temp_dir() . '/audio_upload_' . uniqid('', true) . '.mp3';
        $file->move(dirname($tempPath), basename($tempPath));

        // Compute hash to check for duplicate.
        $hash = hash_file('sha256', $tempPath);
        $original = AudioUpload::query()
            ->where('content_hash', $hash)
            ->whereNull('duplicate_of_id')
            ->oldest('id')
            ->first();

        if ($original) {
            // Duplicate found; create a record referencing the original without storing a new file.
            $upload = AudioUpload::query()->create([
                'original_filename' => $sanitizedName,
                'storage_path' => $original->storage_path,
                'content_hash' => $hash,
                'file_size_bytes' => $fileSize,
                'duration_seconds' => $original->duration_seconds,
                'bitrate_kbps' => $original->bitrate_kbps,
                'sample_rate_hz' => $original->sample_rate_hz,
                'quality_score' => $original->quality_score,
                'is_duration_outlier' => $original->is_duration_outlier,
                'duplicate_of_id' => $original->id,
                'status' => 'completed',
            ]);

            AudioFile::query()->create([
                'title' => $sanitizedName,
                'filename' => $sanitizedName,
                'file_path' => $original->storage_path,
                'size' => $fileSize,
                'duration' => $original->duration_seconds,
                'audio_upload_id' => $upload->id,
                'content_hash' => $hash,
                'bitrate_kbps' => $original->bitrate_kbps,
                'sample_rate_hz' => $original->sample_rate_hz,
                'quality_score' => $original->quality_score,
                'is_duration_outlier' => $original->is_duration_outlier,
            ]);

            // Clean up temporary file.
            @unlink($tempPath);

            return response()->json([
                'id' => $upload->id,
                'duplicate' => [
                    'is_duplicate' => true,
                    'original_upload_id' => $original->id,
                ],
                'analysis' => [
                    'duration_seconds' => $original->duration_seconds,
                    'duration' => $this->formatDuration($original->duration_seconds),
                    'is_duration_outlier' => $original->is_duration_outlier,
                    'quality_score' => $original->quality_score,
                    'bitrate_kbps' => $original->bitrate_kbps,
                    'sample_rate_hz' => $original->sample_rate_hz,
                    'file_size_bytes' => $fileSize,
                ],
            ], 200);
        }

        // Create a pending upload record
        $upload = AudioUpload::query()->create([
            'original_filename' => $sanitizedName,
            'storage_path' => 'audio/' . basename($tempPath), // Placeholder until Job moves it
            'content_hash' => $hash,
            'file_size_bytes' => $fileSize,
            'status' => 'pending',
        ]);

        // Not a duplicate – dispatch background job for processing.
        ProcessAudioUpload::dispatch(
            $upload->id,
            $tempPath,
            $sanitizedName,
            null,
            $fileSize,
        );

        return response()->json([
            'message' => 'File is large and is being processed in the background.',
            'notification' => 'You can check the status using the /api/upload/{id} endpoint.',
            'id' => $upload->id,
            'status' => 'pending',
            'file_size_bytes' => $fileSize,
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

    /**
     * Safely get the uploaded file size, handling potential stat failures.
     */
    private function getUploadedFileSize(UploadedFile $file): int
    {
        try {
            return $file->getSize() ?? 0;
        } catch (\RuntimeException $e) {
            $realPath = $file->getRealPath();
            if ($realPath && file_exists($realPath)) {
                return filesize($realPath);
            }
            return 0;
        }
    }

    public function show(int $id): JsonResponse
    {
        $upload = AudioUpload::find($id);

        if (! $upload) {
            return response()->json(['error' => 'Upload not found'], 404);
        }

        return response()->json([
            'id' => $upload->id,
            'status' => $upload->status,
            'original_filename' => $upload->original_filename,
            'file_size_bytes' => $upload->file_size_bytes,
            'analysis' => [
                'duration_seconds' => $upload->duration_seconds,
                'duration' => $upload->duration_seconds === null ? null : $this->formatDuration($upload->duration_seconds),
                'is_duration_outlier' => $upload->is_duration_outlier,
                'quality_score' => $upload->quality_score,
                'bitrate_kbps' => $upload->bitrate_kbps,
                'sample_rate_hz' => $upload->sample_rate_hz,
            ],
            'is_duplicate' => $upload->duplicate_of_id !== null,
        ]);
    }
}
