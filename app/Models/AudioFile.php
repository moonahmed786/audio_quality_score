<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioFile extends Model
{
    protected $fillable = [
        'title',
        'filename',
        'file_path',
        'size',
        'duration',
        'audio_upload_id',
        'content_hash',
        'bitrate_kbps',
        'sample_rate_hz',
        'quality_score',
        'is_duration_outlier',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'float',
            'size' => 'integer',
            'bitrate_kbps' => 'integer',
            'sample_rate_hz' => 'integer',
            'quality_score' => 'integer',
            'is_duration_outlier' => 'boolean',
        ];
    }

    public function audioUpload(): BelongsTo
    {
        return $this->belongsTo(AudioUpload::class);
    }
}
