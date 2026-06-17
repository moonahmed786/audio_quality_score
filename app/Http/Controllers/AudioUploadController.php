<?php

namespace App\Http\Controllers;

use App\Models\AudioUpload;
use App\Services\Mp3Analyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AudioUploadController extends Controller
{
    public function __invoke(Request $request, Mp3Analyzer $analyzer): JsonResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'extensions:mp3', 'max:51200'],
        ]);

        $file = $validated['file'];
        $hash = hash_file('sha256', $file->getRealPath());
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
            'original_filename' => $file->getClientOriginalName(),
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

    private function formatDuration(float $seconds): string
    {
        $totalSeconds = (int) round($seconds);

        return sprintf('%02d:%02d', intdiv($totalSeconds, 60), $totalSeconds % 60);
    }
}
