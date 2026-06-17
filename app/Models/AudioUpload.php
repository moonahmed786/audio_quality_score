<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AudioUpload extends Model
{
    protected $fillable = [
        'original_filename',
        'storage_path',
        'content_hash',
        'file_size_bytes',
        'duration_seconds',
        'bitrate_kbps',
        'sample_rate_hz',
        'quality_score',
        'is_duration_outlier',
        'duplicate_of_id',
    ];

    protected function casts(): array
    {
        return [
            'duration_seconds' => 'float',
            'bitrate_kbps' => 'integer',
            'sample_rate_hz' => 'integer',
            'quality_score' => 'integer',
            'is_duration_outlier' => 'boolean',
        ];
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(self::class, 'duplicate_of_id');
    }
}
