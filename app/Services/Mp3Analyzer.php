<?php

namespace App\Services;

class Mp3Analyzer
{
    private const BITRATES = [
        '1:3' => [null, 32, 40, 48, 56, 64, 80, 96, 112, 128, 160, 192, 224, 256, 320],
        '2:3' => [null, 8, 16, 24, 32, 40, 48, 56, 64, 80, 96, 112, 128, 144, 160],
    ];

    private const SAMPLE_RATES = [
        3 => [44100, 48000, 32000],
        2 => [22050, 24000, 16000],
        0 => [11025, 12000, 8000],
    ];

    public function analyze(string $path): AudioAnalysisResult
    {
        $metadata = $this->readFrameMetadata($path);
        $qualityScore = $this->score(
            fileSizeBytes: filesize($path) ?: 0,
            durationSeconds: $metadata['duration_seconds'],
            bitrateKbps: $metadata['bitrate_kbps'],
            sampleRateHz: $metadata['sample_rate_hz'],
        );

        return new AudioAnalysisResult(
            durationSeconds: $metadata['duration_seconds'],
            bitrateKbps: $metadata['bitrate_kbps'],
            sampleRateHz: $metadata['sample_rate_hz'],
            qualityScore: $qualityScore,
            isDurationOutlier: $this->isDurationOutlier($metadata['duration_seconds']),
        );
    }

    private function readFrameMetadata(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            return $this->emptyMetadata();
        }

        try {
            $offset = $this->skipId3v2Tag($handle);
            $duration = 0.0;
            $bitrates = [];
            $sampleRates = [];
            $frames = 0;

            while (! feof($handle)) {
                fseek($handle, $offset);
                $header = fread($handle, 4);

                if (strlen($header) < 4) {
                    break;
                }

                $frame = $this->parseFrameHeader($header);

                if ($frame === null) {
                    $offset++;

                    continue;
                }

                $duration += $frame['samples_per_frame'] / $frame['sample_rate_hz'];
                $bitrates[] = $frame['bitrate_kbps'];
                $sampleRates[] = $frame['sample_rate_hz'];
                $frames++;
                $offset += $frame['frame_length'];
            }

            if ($frames === 0) {
                return $this->emptyMetadata();
            }

            return [
                'duration_seconds' => round($duration, 3),
                'bitrate_kbps' => (int) round(array_sum($bitrates) / count($bitrates)),
                'sample_rate_hz' => (int) round(array_sum($sampleRates) / count($sampleRates)),
            ];
        } finally {
            fclose($handle);
        }
    }

    private function parseFrameHeader(string $header): ?array
    {
        $bytes = unpack('C4', $header);

        if (($bytes[1] !== 0xFF) || (($bytes[2] & 0xE0) !== 0xE0)) {
            return null;
        }

        $versionBits = ($bytes[2] >> 3) & 0x03;
        $layerBits = ($bytes[2] >> 1) & 0x03;
        $bitrateIndex = ($bytes[3] >> 4) & 0x0F;
        $sampleRateIndex = ($bytes[3] >> 2) & 0x03;
        $padding = ($bytes[3] >> 1) & 0x01;

        if ($versionBits === 1 || $layerBits !== 1 || $bitrateIndex === 0 || $bitrateIndex === 15 || $sampleRateIndex === 3) {
            return null;
        }

        $versionGroup = $versionBits === 3 ? '1' : '2';
        $bitrateKbps = self::BITRATES[$versionGroup.':3'][$bitrateIndex] ?? null;
        $sampleRateHz = self::SAMPLE_RATES[$versionBits][$sampleRateIndex] ?? null;

        if ($bitrateKbps === null || $sampleRateHz === null) {
            return null;
        }

        $samplesPerFrame = $versionBits === 3 ? 1152 : 576;
        $coefficient = $versionBits === 3 ? 144 : 72;
        $frameLength = (int) floor(($coefficient * $bitrateKbps * 1000) / $sampleRateHz) + $padding;

        return [
            'bitrate_kbps' => $bitrateKbps,
            'sample_rate_hz' => $sampleRateHz,
            'samples_per_frame' => $samplesPerFrame,
            'frame_length' => $frameLength,
        ];
    }

    private function skipId3v2Tag($handle): int
    {
        rewind($handle);
        $header = fread($handle, 10);

        if (strlen($header) !== 10 || substr($header, 0, 3) !== 'ID3') {
            return 0;
        }

        $bytes = unpack('C4', substr($header, 6, 4));

        return 10
            + (($bytes[1] & 0x7F) << 21)
            + (($bytes[2] & 0x7F) << 14)
            + (($bytes[3] & 0x7F) << 7)
            + ($bytes[4] & 0x7F);
    }

    private function score(int $fileSizeBytes, ?float $durationSeconds, ?int $bitrateKbps, ?int $sampleRateHz): int
    {
        $score = 1;

        $score += match (true) {
            $bitrateKbps === null => 0,
            $bitrateKbps >= 256 => 4,
            $bitrateKbps >= 192 => 3,
            $bitrateKbps >= 128 => 2,
            $bitrateKbps >= 96 => 1,
            default => 0,
        };

        $score += match (true) {
            $sampleRateHz === null => 0,
            $sampleRateHz >= 44100 => 3,
            $sampleRateHz >= 32000 => 2,
            $sampleRateHz >= 22050 => 1,
            default => 0,
        };

        if ($durationSeconds !== null && $durationSeconds > 0) {
            $bytesPerSecond = $fileSizeBytes / $durationSeconds;
            $score += match (true) {
                $bytesPerSecond >= 24000 => 2,
                $bytesPerSecond >= 16000 => 1,
                default => 0,
            };
        }

        return max(1, min(10, $score));
    }

    private function isDurationOutlier(?float $durationSeconds): bool
    {
        return $durationSeconds === null || $durationSeconds < 1 || $durationSeconds > 3600;
    }

    private function emptyMetadata(): array
    {
        return [
            'duration_seconds' => null,
            'bitrate_kbps' => null,
            'sample_rate_hz' => null,
        ];
    }
}
