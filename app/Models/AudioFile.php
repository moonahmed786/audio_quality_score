<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AudioFile extends Model
{
    protected $fillable = [
        'title',
        'filename',
        'file_path',
        'size',
        'duration',
    ];

    protected function casts(): array
    {
        return [
            'duration' => 'float',
            'size' => 'integer',
        ];
    }
}
