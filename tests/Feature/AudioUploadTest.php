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

    private function createTokenForUser($user): string
    {
        $plainToken = \Illuminate\Support\Str::random(40);
        \App\Models\ApiToken::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'name' => 'test_token',
        ]);
        return $plainToken;
    }

    public function test_upload_returns_analysis_and_detects_exact_duplicate_by_content(): void
    {
        Storage::fake('local');
        $bytes = $this->mp3Bytes(frameCount: 100);

        $user = \App\Models\User::factory()->create();
        $token = $this->createTokenForUser($user);

        $first = $this->withToken($token)->postJson('/api/upload', [
            'file' => UploadedFile::fake()->createWithContent('voice-note.mp3', $bytes),
        ]);

        $first->assertCreated()
            ->assertJsonPath('duplicate.is_duplicate', false)
            ->assertJsonPath('duplicate.original_upload_id', null)
            ->assertJsonPath('analysis.bitrate_kbps', 128)
            ->assertJsonPath('analysis.sample_rate_hz', 44100)
            ->assertJsonPath('analysis.quality_score', 6)
            ->assertJsonPath('analysis.is_duration_outlier', false);

        $second = $this->withToken($token)->postJson('/api/upload', [
            'file' => UploadedFile::fake()->createWithContent('renamed-track.mp3', $bytes),
        ]);

        $second->assertOk()
            ->assertJsonPath('duplicate.is_duplicate', true)
            ->assertJsonPath('duplicate.original_upload_id', $first->json('id'));
    }

    public function test_large_upload_dispatches_job_and_returns_pending_status(): void
    {
        Storage::fake('local');
        \Illuminate\Support\Facades\Queue::fake();

        // Generate >25MB MP3 to trigger async handling
        // 25MB = 26214400 bytes. Frame size = 417. We need ~63000 frames.
        $bytes = $this->mp3Bytes(frameCount: 63000);

        $user = \App\Models\User::factory()->create();
        $token = $this->createTokenForUser($user);

        $response = $this->withToken($token)->postJson('/api/upload', [
            'file' => UploadedFile::fake()->createWithContent('large-voice-note.mp3', $bytes),
        ]);

        $response->assertStatus(202)
            ->assertJsonPath('status', 'pending');

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ProcessAudioUpload::class);
    }

    public function test_upload_rejects_non_mp3_extensions(): void
    {
        Storage::fake('local');

        $user = \App\Models\User::factory()->create();
        $token = $this->createTokenForUser($user);

        $response = $this->withToken($token)->postJson('/api/upload', [
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
