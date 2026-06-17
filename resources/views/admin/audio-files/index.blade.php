<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                    {{ __('Audio Library') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Manage and organize your audio files</p>
            </div>
            <a href="{{ route('admin.audio-files.create') }}"
               class="inline-flex items-center px-5 py-2.5 bg-violet-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition shadow-md">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload New
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <!-- Search Bar -->
            <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6">
                <form method="GET" action="{{ route('admin.audio-files.index') }}" class="flex gap-3">
                    <div class="flex-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Search by title or filename..."
                               class="w-full pl-11 pr-4 py-2.5 rounded-xl border-gray-200 bg-gray-50 focus:bg-white focus:border-violet-500 focus:ring-violet-500 text-sm transition">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 bg-violet-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:bg-violet-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition">
                        Search
                    </button>
                    @if (request('search'))
                        <a href="{{ route('admin.audio-files.index') }}"
                           class="inline-flex items-center px-5 py-2.5 bg-white border border-gray-300 rounded-xl font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition">
                            Clear
                        </a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead class="bg-gray-50/70">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-1/3">Player</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('admin.audio-files.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'title', 'direction' => request('sort') === 'title' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700 transition">
                                        Title
                                        @if (request('sort') === 'title')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden lg:table-cell">
                                    <a href="{{ route('admin.audio-files.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'filename', 'direction' => request('sort') === 'filename' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700 transition">
                                        Filename
                                        @if (request('sort') === 'filename')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    <a href="{{ route('admin.audio-files.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'size', 'direction' => request('sort') === 'size' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700 transition">
                                        Size
                                        @if (request('sort') === 'size')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden xl:table-cell">Details</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">
                                    <a href="{{ route('admin.audio-files.index', array_merge(request()->except('sort', 'direction'), ['sort' => 'created_at', 'direction' => request('sort') === 'created_at' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}" class="flex items-center gap-1 hover:text-gray-700 transition">
                                        Uploaded
                                        @if (request('sort') === 'created_at')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                        @endif
                                    </a>
                                </th>
                                <th scope="col" class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            @forelse ($files as $file)
                                <tr class="hover:bg-gray-50/50 transition group">
                                    <td class="px-6 py-4">
                                        <x-audio-player :src="asset('storage/' . $file->file_path)" :id="$file->id" />
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-semibold text-gray-900">{{ $file->title }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5 md:hidden">{{ $file->filename }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden lg:table-cell">
                                        {{ $file->filename }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        @if ($file->size > 1024 * 1024)
                                            {{ number_format($file->size / 1024 / 1024, 2) }} MB
                                        @else
                                            {{ number_format($file->size / 1024, 1) }} KB
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 hidden xl:table-cell">
                                        <div class="flex flex-wrap gap-1">
                                            @if ($file->bitrate_kbps)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                                    {{ $file->bitrate_kbps }} kbps
                                                </span>
                                            @endif
                                            @if ($file->sample_rate_hz)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600">
                                                    {{ $file->sample_rate_hz }} Hz
                                                </span>
                                            @endif
                                            @if ($file->quality_score !== null)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $file->quality_score >= 80 ? 'bg-emerald-50 text-emerald-600' : ($file->quality_score >= 50 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">
                                                    Q: {{ $file->quality_score }}
                                                </span>
                                            @endif
                                            @if ($file->is_duration_outlier)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-50 text-rose-600" title="Duration outlier detected">
                                                    Outlier
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400 hidden md:table-cell">
                                        {{ $file->created_at->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium">
                                        <div class="inline-flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.audio-files.edit', $file) }}"
                                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-violet-600 text-white rounded-lg text-xs font-semibold hover:bg-violet-700 transition shadow-sm shrink-0">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                <span>Edit</span>
                                            </a>
                                            <form method="POST" action="{{ route('admin.audio-files.destroy', $file) }}" class="inline shrink-0"
                                                  onsubmit="return confirm('Delete this audio file permanently?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-rose-600 text-white rounded-lg text-xs font-semibold hover:bg-rose-700 transition shadow-sm">
                                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    <span>Delete</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center">
                                        <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-500">No audio files found</p>
                                        <a href="{{ route('admin.audio-files.create') }}" class="inline-flex items-center gap-1 text-violet-600 hover:text-violet-800 font-semibold text-sm mt-2">
                                            Upload your first file
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($files->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
                        {{ $files->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
