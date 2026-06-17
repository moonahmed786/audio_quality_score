<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAudioFileRequest;
use App\Http\Requests\UpdateAudioFileRequest;
use App\Models\AudioFile;
use App\Services\AudioFileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AudioFileController extends Controller
{
    public function __construct(private AudioFileService $service) {}

    public function index(Request $request): View
    {
        $files = $this->service->list([
            'search' => $request->input('search'),
            'sort' => $request->input('sort'),
            'direction' => $request->input('direction'),
        ]);

        return view('admin.audio-files.index', compact('files'));
    }

    public function create(): View
    {
        return view('admin.audio-files.create');
    }

    public function store(StoreAudioFileRequest $request): RedirectResponse
    {
        $this->service->store($request->validated());

        return redirect()->route('admin.audio-files.index')->with('success', 'Audio file uploaded successfully.');
    }

    public function edit(AudioFile $audioFile): View
    {
        return view('admin.audio-files.edit', compact('audioFile'));
    }

    public function update(UpdateAudioFileRequest $request, AudioFile $audioFile): RedirectResponse
    {
        $this->service->update($audioFile, $request->validated());

        return redirect()->route('admin.audio-files.index')->with('success', 'Audio file updated successfully.');
    }

    public function destroy(AudioFile $audioFile): RedirectResponse
    {
        $this->service->delete($audioFile);

        return redirect()->route('admin.audio-files.index')->with('success', 'Audio file deleted successfully.');
    }
}
