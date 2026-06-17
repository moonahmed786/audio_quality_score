<?php

namespace Tests\Feature;

use App\Models\AudioUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AudioUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_returns_analysis_and_detects_exact_duplicate_by_content(): void
    {
        Storage::fake('local');
        $bytes = $this->mp3Bytes(frameCount: 100);

        $first = $this->post('/api/upload', [
            'file' => UploadedFile::fake()->createWithContent('voice-note.mp3', $bytes),
        ]);

        $first->assertCreated()
            ->assertJsonPath('duplicate.is_duplicate', false)
            ->assertJsonPath('duplicate.original_upload_id', null)
            ->assertJsonPath('analysis.bitrate_kbps', 128)
            ->assertJsonPath('analysis.sample_rate_hz', 44100)
            ->assertJsonPath('analysis.quality_score', 6)
            ->assertJsonPath('analysis.is_duration_outlier', false);

        $second = $this->post('/api/upload', [
            'file' => UploadedFile::fake()->createWithContent('renamed-track.mp3', $bytes),
        ]);

        $second->assertCreated()
            ->assertJsonPath('duplicate.is_duplicate', true)
            ->assertJsonPath('duplicate.original_upload_id', $first->json('id'));

        $this->assertDatabaseCount(AudioUpload::class, 2);
        $this->assertDatabaseHas(AudioUpload::class, [
            'id' => $second->json('id'),
            'duplicate_of_id' => $first->json('id'),
        ]);
    }

    public function test_upload_rejects_non_mp3_extensions(): void
    {
        Storage::fake('local');

        $response = $this->post('/api/upload', [
            'file' => UploadedFile::fake()->createWithContent('notes.txt', 'not audio'),
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('file');
    }

    private function mp3Bytes(int $frameCount): string
    {
        $header = "\xFF\xFB\x90\x64";
        $frame = $header.str_repeat("\0", 413);

        return str_repeat($frame, $frameCount);
    }
}
