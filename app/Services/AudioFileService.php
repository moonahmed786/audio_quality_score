<?php

namespace App\Services;

use App\Models\AudioFile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class AudioFileService
{
    public function __construct(private Mp3Analyzer $analyzer) {}

    public function list(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = AudioFile::query();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search): void {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('filename', 'like', "%{$search}%");
            });
        }

        $sort = $filters['sort'] ?? 'created_at';
        $direction = $filters['direction'] ?? 'desc';
        $allowedSorts = ['title', 'filename', 'size', 'duration', 'created_at'];

        if (in_array($sort, $allowedSorts, true)) {
            $query->orderBy($sort, $direction === 'asc' ? 'asc' : 'desc');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function store(array $data): AudioFile
    {
        /** @var UploadedFile $file */
        $file = $data['file'];
        $path = $file->store('audio', 'public');

        $analysis = $this->analyzer->analyze(Storage::disk('public')->path($path));

        return AudioFile::query()->create([
            'title' => $data['title'],
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'size' => $file->getSize(),
            'duration' => $analysis->durationSeconds,
        ]);
    }

    public function update(AudioFile $audioFile, array $data): AudioFile
    {
        if (! empty($data['file'])) {
            /** @var UploadedFile $file */
            $file = $data['file'];

            if ($audioFile->file_path && Storage::disk('public')->exists($audioFile->file_path)) {
                Storage::disk('public')->delete($audioFile->file_path);
            }

            $path = $file->store('audio', 'public');
            $analysis = $this->analyzer->analyze(Storage::disk('public')->path($path));

            $audioFile->fill([
                'filename' => $file->getClientOriginalName(),
                'file_path' => $path,
                'size' => $file->getSize(),
                'duration' => $analysis->durationSeconds,
            ]);
        }

        if (isset($data['title'])) {
            $audioFile->title = $data['title'];
        }

        $audioFile->save();

        return $audioFile;
    }

    public function delete(AudioFile $audioFile): void
    {
        if ($audioFile->file_path && Storage::disk('public')->exists($audioFile->file_path)) {
            Storage::disk('public')->delete($audioFile->file_path);
        }

        $audioFile->delete();
    }
}
