<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('admin.audio-files.create') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-violet-600 to-fuchsia-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-violet-700 hover:to-fuchsia-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 transition shadow-lg shadow-violet-500/25 hover:shadow-violet-500/40">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Upload Audio
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Total Files -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6 relative overflow-hidden group hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-violet-100 to-fuchsia-50 rounded-bl-full -mr-8 -mt-8 opacity-60 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-500 to-fuchsia-500 flex items-center justify-center shadow-lg shadow-violet-500/25">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Files</span>
                        </div>
                        <div class="text-4xl font-bold text-gray-900 tracking-tight">{{ $stats['total_files'] }}</div>
                        <div class="text-sm text-gray-400 mt-1">Audio files in library</div>
                    </div>
                </div>

                <!-- Storage -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6 relative overflow-hidden group hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-100 to-teal-50 rounded-bl-full -mr-8 -mt-8 opacity-60 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-500 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Storage Used</span>
                        </div>
                        <div class="text-4xl font-bold text-gray-900 tracking-tight">
                            @if ($stats['total_size'] > 0)
                                @if ($stats['total_size'] > 1024 * 1024 * 1024)
                                    {{ number_format($stats['total_size'] / 1024 / 1024 / 1024, 2) }}<span class="text-lg text-gray-400 font-medium ml-1">GB</span>
                                @else
                                    {{ number_format($stats['total_size'] / 1024 / 1024, 2) }}<span class="text-lg text-gray-400 font-medium ml-1">MB</span>
                                @endif
                            @else
                                0<span class="text-lg text-gray-400 font-medium ml-1">MB</span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-400 mt-1">Total storage consumed</div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 p-6 relative overflow-hidden group hover:shadow-md transition-all duration-300">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-amber-100 to-orange-50 rounded-bl-full -mr-8 -mt-8 opacity-60 group-hover:scale-110 transition-transform duration-500"></div>
                    <div class="relative">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/25">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Recent</span>
                        </div>
                        <div class="text-4xl font-bold text-gray-900 tracking-tight">{{ $stats['recent_uploads']->count() }}</div>
                        <div class="text-sm text-gray-400 mt-1">Uploads this session</div>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100/80 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-slate-100 flex items-center justify-center">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Recent Uploads</h3>
                            <p class="text-sm text-gray-400">Latest audio files added</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.audio-files.index') }}"
                       class="group inline-flex items-center gap-1 text-sm font-semibold text-violet-600 hover:text-violet-700 transition">
                        View all
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
                <div class="divide-y divide-gray-50">
                    @forelse ($stats['recent_uploads'] as $file)
                        <div class="p-4 flex items-center justify-between hover:bg-gray-50/50 transition group">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-50 to-fuchsia-50 flex items-center justify-center shrink-0 group-hover:from-violet-100 group-hover:to-fuchsia-100 transition">
                                    <svg class="w-5 h-5 text-violet-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="text-sm font-bold text-gray-900">{{ $file->title }}</div>
                                    <div class="text-xs text-gray-400 mt-0.5">{{ $file->filename }} &middot; {{ number_format($file->size / 1024, 1) }} KB @if($file->duration)&middot; {{ sprintf('%02d:%02d', intdiv((int) round($file->duration), 60), (int) round($file->duration) % 60) }}@endif</div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-gray-400">{{ $file->created_at->diffForHumans() }}</span>
                                <a href="{{ route('admin.audio-files.edit', $file) }}" class="opacity-0 group-hover:opacity-100 transition text-violet-600 hover:text-violet-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-10 text-gray-400 text-center">
                            <div class="w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium">No audio files uploaded yet</p>
                            <a href="{{ route('admin.audio-files.create') }}" class="inline-flex items-center gap-1 text-violet-600 hover:text-violet-800 font-semibold text-sm mt-2">
                                Upload your first file
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
