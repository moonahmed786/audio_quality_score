<?php

namespace Tests\Unit;

use App\Services\Mp3Analyzer;
use PHPUnit\Framework\TestCase;

class Mp3AnalyzerTest extends TestCase
{
    public function test_it_reads_basic_mp3_frame_metadata_and_scores_quality(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'mp3-test-');
        file_put_contents($path, $this->mp3Bytes(frameCount: 100));

        try {
            $analysis = (new Mp3Analyzer)->analyze($path);

            $this->assertSame(128, $analysis->bitrateKbps);
            $this->assertSame(44100, $analysis->sampleRateHz);
            $this->assertEqualsWithDelta(2.612, $analysis->durationSeconds, 0.001);
            $this->assertSame(6, $analysis->qualityScore);
            $this->assertFalse($analysis->isDurationOutlier);
        } finally {
            @unlink($path);
        }
    }

    private function mp3Bytes(int $frameCount): string
    {
        $header = "\xFF\xFB\x90\x64";
        $frame = $header.str_repeat("\0", 413);

        return str_repeat($frame, $frameCount);
    }
}
