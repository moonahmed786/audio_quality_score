@props(['src', 'id'])

<div x-data="{
    playing: false,
    audio: null,
    init() {
        this.audio = new Audio('{{ $src }}');
        this.audio.onended = () => this.playing = false;
        this.audio.onpause = () => this.playing = false;
    },
    toggle() {
        if (this.playing) {
            this.audio.pause();
        } else {
            this.audio.play();
        }
        this.playing = !this.playing;
    }
}" class="inline-flex items-center gap-2">
    <button
        type="button"
        @click="toggle()"
        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-indigo-600 hover:bg-indigo-700 text-white transition"
    >
        <template x-if="!playing">
            <svg class="w-4 h-4 ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4l12 6-12 6z"/>
            </svg>
        </template>
        <template x-if="playing">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </template>
    </button>
</div>
