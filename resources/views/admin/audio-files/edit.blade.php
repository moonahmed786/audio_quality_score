<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Audio File') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('admin.audio-files.update', $audioFile) }}" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $audioFile->title) }}" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-3 py-2 border @error('title') border-red-500 @enderror">
                            @error('title')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="bg-gray-50 rounded-md p-4">
                            <div class="text-sm font-medium text-gray-700 mb-2">Current File</div>
                            <div class="text-sm text-gray-600 mb-2">{{ $audioFile->filename }}</div>
                            <div data-player>
                                <x-audio-player :src="asset('storage/' . $audioFile->file_path)" :id="$audioFile->id" />
                            </div>
                        </div>

                        <div>
                            <label for="file" class="block text-sm font-medium text-gray-700">Replace File (optional)</label>
                            <input type="file" name="file" id="file" accept=".mp3,audio/mpeg"
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 @error('file') border-red-500 @enderror">
                            <p class="mt-1 text-xs text-gray-500">Upload a new MP3 to replace the existing file. Maximum: 64MB.</p>
                            @error('file')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center gap-4">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Update
                            </button>
                            <a href="{{ route('admin.audio-files.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
