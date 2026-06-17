<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <x-flash-message />

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Audio Files</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_files'] }}</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Total Storage Used</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">
                        @if ($stats['total_size'] > 0)
                            {{ number_format($stats['total_size'] / 1024 / 1024, 2) }} MB
                        @else
                            0 MB
                        @endif
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm font-medium text-gray-500">Recent Uploads</div>
                    <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['recent_uploads']->count() }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Recent Uploads</h3>
                </div>
                <div class="divide-y divide-gray-200">
                    @forelse ($stats['recent_uploads'] as $file)
                        <div class="p-4 flex items-center justify-between">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $file->title }}</div>
                                <div class="text-xs text-gray-500">{{ $file->filename }} &middot; {{ number_format($file->size / 1024, 2) }} KB</div>
                            </div>
                            <div class="text-xs text-gray-400">{{ $file->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="p-6 text-gray-500 text-center">No audio files uploaded yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
