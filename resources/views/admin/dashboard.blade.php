<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                {{ __('Dashboard') }}
            </h2>
            <a href="{{ route('admin.audio-files.create') }}"
               class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-violet-600 to-indigo-600 border border-transparent rounded-2xl font-bold text-sm text-white hover:from-violet-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 hover:shadow-lg hover:shadow-violet-500/30 transform hover:-translate-y-0.5 transition-all duration-300">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">
                <!-- Total Files -->
                <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-violet-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total Files</span>
                            <div class="text-3xl font-bold text-gray-900 tracking-tight mt-3">{{ $stats['total_files'] }}</div>
                            <div class="text-sm text-gray-400 mt-1">Audio files in library</div>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-violet-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-violet-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Storage -->
                <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Storage Used</span>
                            <div class="text-3xl font-bold text-gray-900 tracking-tight mt-3">
                                @if ($stats['total_size'] > 0)
                                    @if ($stats['total_size'] > 1024 * 1024 * 1024)
                                        {{ number_format($stats['total_size'] / 1024 / 1024 / 1024, 2) }}<span class="text-base text-gray-400 font-medium ml-1">GB</span>
                                    @else
                                        {{ number_format($stats['total_size'] / 1024 / 1024, 2) }}<span class="text-base text-gray-400 font-medium ml-1">MB</span>
                                    @endif
                                @else
                                    0<span class="text-base text-gray-400 font-medium ml-1">MB</span>
                                @endif
                            </div>
                            <div class="text-sm text-gray-400 mt-1">Total storage consumed</div>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-gray-100 p-6 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Recent</span>
                            <div class="text-3xl font-bold text-gray-900 tracking-tight mt-3">{{ $stats['recent_uploads']->count() }}</div>
                            <div class="text-sm text-gray-400 mt-1">Latest uploads shown</div>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Uploads -->
            <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-gray-100 overflow-hidden shadow-sm hover:shadow-lg transition-shadow duration-300">
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
                                <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center shrink-0 group-hover:bg-violet-100 transition">
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
                                @if ($file->quality_score !== null)
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold {{ $file->quality_score >= 80 ? 'bg-emerald-50 text-emerald-600' : ($file->quality_score >= 50 ? 'bg-amber-50 text-amber-600' : 'bg-rose-50 text-rose-600') }}">
                                        Q{{ $file->quality_score }}
                                    </span>
                                @endif
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
                            <a href="{{ route('admin.audio-files.create') }}" class="inline-flex items-center gap-1 text-violet-600 hover:text-violet-700 font-bold text-sm mt-2 transition-colors">
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
