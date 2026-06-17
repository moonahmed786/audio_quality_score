@props(['src', 'id', 'minimal' => false])

<div x-data="{
    playing: false,
    currentTime: 0,
    duration: 0,
    progress: 0,
    volume: 1,
    loaded: false,
    error: null,
    init() {
        this.$refs.audio.src = '{{ $src }}';
        this.$refs.audio.volume = this.volume;
        this.$refs.audio.preload = 'metadata';
    },
    onLoadedMeta() {
        this.duration = this.$refs.audio.duration || 0;
        this.loaded = true;
        this.error = null;
    },
    onTimeUpdate() {
        this.currentTime = this.$refs.audio.currentTime || 0;
        this.duration = this.$refs.audio.duration || this.duration;
        this.progress = this.duration > 0 ? (this.currentTime / this.duration) * 100 : 0;
    },
    onEnded() {
        this.playing = false;
        this.progress = 0;
        this.currentTime = 0;
    },
    onError() {
        this.error = 'Failed to load audio';
        this.playing = false;
    },
    toggle() {
        if (!this.$refs.audio) return;
        if (this.playing) {
            this.$refs.audio.pause();
        } else {
            const playPromise = this.$refs.audio.play();
            if (playPromise !== undefined) {
                playPromise.catch(() => {
                    this.playing = false;
                });
            }
        }
    },
    seek(e) {
        if (!this.$refs.audio || !this.duration) return;
        const rect = e.currentTarget.getBoundingClientRect();
        const clickX = e.clientX - rect.left;
        const newProgress = Math.max(0, Math.min(1, clickX / rect.width));
        this.$refs.audio.currentTime = newProgress * this.duration;
    },
    skip(seconds) {
        if (!this.$refs.audio || !this.duration) return;
        this.$refs.audio.currentTime = Math.max(0, Math.min(this.duration, this.$refs.audio.currentTime + seconds));
    },
    setVolume(e) {
        this.volume = e.target.value;
        if (this.$refs.audio) {
            this.$refs.audio.volume = this.volume;
        }
    },
    formatTime(seconds) {
        if (!seconds || isNaN(seconds) || !isFinite(seconds)) return '00:00';
        const total = Math.floor(seconds);
        const m = Math.floor(total / 60);
        const s = total % 60;
        return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
    }
}" x-init="init()" class="flex items-center gap-3 w-full max-w-md">
    <!-- Hidden Audio Element -->
    <audio x-ref="audio"
           @loadedmetadata="onLoadedMeta()"
           @timeupdate="onTimeUpdate()"
           @ended="onEnded()"
           @play="playing = true"
           @pause="playing = false"
           x-on:error="onError()"></audio>

    <!-- Play/Pause -->
    <button type="button" @click="toggle()"
            class="shrink-0 inline-flex items-center justify-center w-10 h-10 rounded-full bg-violet-600 hover:bg-violet-700 text-white transition shadow-md active:scale-95"
            :class="{ 'bg-rose-600 hover:bg-rose-700': error }">
        <template x-if="error">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </template>
        <template x-if="!error && !playing">
            <svg class="w-5 h-5 ml-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M4 4l12 6-12 6z"/>
            </svg>
        </template>
        <template x-if="!error && playing">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
        </template>
    </button>

    @if(!$minimal)
    <!-- Skip Backward -->
    <button type="button" @click="skip(-10)"
            class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-gray-500 hover:text-white hover:bg-violet-600 transition" title="-10s">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0019 16V8a1 1 0 00-1.6-.8l-5.333 4zM4.066 11.2a1 1 0 000 1.6l5.334 4A1 1 0 0011 16V8a1 1 0 00-1.6-.8l-5.334 4z"/>
        </svg>
    </button>

    <!-- Skip Forward -->
    <button type="button" @click="skip(10)"
            class="shrink-0 w-7 h-7 rounded-full flex items-center justify-center text-gray-500 hover:text-white hover:bg-violet-600 transition" title="+10s">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.933 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 005 8v8a1 1 0 001.6.8l5.334-4zM19.933 12.8a1 1 0 000-1.6l-5.334-4A1 1 0 0013 8v8a1 1 0 001.6.8l5.334-4z"/>
        </svg>
    </button>

    <!-- Progress Bar -->
    <div class="flex-1 flex flex-col gap-1.5 min-w-0">
        <template x-if="error">
            <div class="text-xs text-rose-600 font-medium" x-text="error"></div>
        </template>
        <template x-if="!error">
            <div>
                <div class="relative h-2 bg-gray-200 rounded-full cursor-pointer group overflow-hidden"
                     @click="seek($event)">
                    <div class="absolute top-0 left-0 h-2 bg-violet-600 rounded-full transition-all duration-100"
                         :style="`width: ${progress}%`"></div>
                    <div class="absolute top-1/2 -translate-y-1/2 w-3 h-3 bg-white border-2 border-violet-600 rounded-full shadow opacity-0 group-hover:opacity-100 transition-opacity"
                         :style="`left: calc(${progress}% - 6px)`"></div>
                </div>
                <div class="flex justify-between text-[11px] text-gray-500 font-medium mt-1">
                    <span x-text="formatTime(currentTime)"></span>
                    <span x-text="formatTime(duration)"></span>
                </div>
            </div>
        </template>
    </div>

    <!-- Volume -->
    <div class="shrink-0 flex items-center gap-1.5" title="Volume">
        <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
        </svg>
        <input type="range" min="0" max="1" step="0.05" :value="volume" @input="setVolume($event)"
               class="w-14 h-1.5 bg-gray-200 rounded-full appearance-none cursor-pointer accent-violet-600">
    </div>
    @endif
</div>
