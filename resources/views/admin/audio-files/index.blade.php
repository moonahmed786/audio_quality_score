<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Audio Files') }}
            </h2>
            <a href="{{ route('admin.audio-files.create') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Upload New
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('admin.audio-files.index') }}" class="flex gap-4">
                        <div class="flex-1">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Search by title or filename..."
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm px-4 py-2 border">
                        </div>
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            Search
                        </button>
                        @if (request('search'))
                            <a href="{{ route('admin.audio-files.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                Clear
                            </a>
                        @endif
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Play</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('admin.audio-files.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'title', 'direction' => request('sort') === 'title' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        Title
                                        @if (request('sort') === 'title')
                                            <span class="text-gray-400">{{ request('direction') === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('admin.audio-files.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'filename', 'direction' => request('sort') === 'filename' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        Filename
                                        @if (request('sort') === 'filename')
                                            <span class="text-gray-400">{{ request('direction') === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('admin.audio-files.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'size', 'direction' => request('sort') === 'size' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        Size
                                        @if (request('sort') === 'size')
                                            <span class="text-gray-400">{{ request('direction') === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('admin.audio-files.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'duration', 'direction' => request('sort') === 'duration' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        Duration
                                        @if (request('sort') === 'duration')
                                            <span class="text-gray-400">{{ request('direction') === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('admin.audio-files.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'created_at', 'direction' => request('sort') === 'created_at' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700">
                                        Uploaded
                                        @if (request('sort') === 'created_at')
                                            <span class="text-gray-400">{{ request('direction') === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($files as $file)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap" data-player>
                                        <x-audio-player :src="asset('storage/' . $file->file_path)" :id="$file->id" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $file->title }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $file->filename }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ number_format($file->size / 1024, 2) }} KB
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($file->duration)
                                            {{ sprintf('%02d:%02d', intdiv((int) round($file->duration), 60), (int) round($file->duration) % 60) }}
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $file->created_at->format('M d, Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.audio-files.edit', $file) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                                        <form method="POST" action="{{ route('admin.audio-files.destroy', $file) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this audio file?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">
                                        No audio files found.
                                        <a href="{{ route('admin.audio-files.create') }}" class="text-indigo-600 hover:text-indigo-900 ml-1">Upload one now.</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($files->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $files->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
