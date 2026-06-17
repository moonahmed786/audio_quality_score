<?php

namespace App\Services;

final readonly class AudioAnalysisResult
{
    public function __construct(
        public ?float $durationSeconds,
        public ?int $bitrateKbps,
        public ?int $sampleRateHz,
        public int $qualityScore,
        public bool $isDurationOutlier,
    ) {}
}
