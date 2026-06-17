<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.audio-files.index') }}" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h2 class="font-semibold text-2xl text-gray-900 leading-tight">
                    {{ __('Edit Audio File') }}
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Update metadata or replace the file</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <form id="editForm"
                          method="POST"
                          action="{{ route('admin.audio-files.update', $audioFile) }}"
                          enctype="multipart/form-data"
                          x-data="{
                              dragging: false,
                              newFile: null,
                              newFileName: '',
                              newFileSize: '',
                              uploading: false,
                              progress: 0,
                              handleDrop(e) {
                                  this.dragging = false;
                                  const dropped = e.dataTransfer.files[0];
                                  if (dropped && dropped.name.endsWith('.mp3')) {
                                      this.newFile = dropped;
                                      this.setFileInfo(dropped);
                                      document.getElementById('fileInput').files = e.dataTransfer.files;
                                  }
                              },
                              handleFileSelect(e) {
                                  const selected = e.target.files[0];
                                  if (selected) {
                                      this.newFile = selected;
                                      this.setFileInfo(selected);
                                  }
                              },
                              setFileInfo(f) {
                                  this.newFileName = f.name;
                                  this.newFileSize = f.size > 1024 * 1024
                                      ? (f.size / 1024 / 1024).toFixed(2) + ' MB'
                                      : (f.size / 1024).toFixed(1) + ' KB';
                              },
                              removeFile() {
                                  this.newFile = null;
                                  this.newFileName = '';
                                  this.newFileSize = '';
                                  document.getElementById('fileInput').value = '';
                              },
                              submitForm(e) {
                                  e.preventDefault();
                                  this.uploading = true;
                                  this.progress = 0;

                                  const form = document.getElementById('editForm');
                                  const formData = new FormData(form);
                                  const xhr = new XMLHttpRequest();

                                  xhr.upload.addEventListener('progress', (ev) => {
                                      if (ev.lengthComputable) {
                                          this.progress = Math.round((ev.loaded / ev.total) * 100);
                                      }
                                  });

                                  xhr.addEventListener('load', () => {
                                      this.uploading = false;
                                      if (xhr.status >= 200 && xhr.status < 300) {
                                          window.dispatchEvent(new CustomEvent('toast', {
                                              detail: { message: 'Audio file updated successfully!', type: 'success' }
                                          }));
                                          setTimeout(() => window.location.href = '{{ route('admin.audio-files.index') }}', 800);
                                      } else {
                                          window.dispatchEvent(new CustomEvent('toast', {
                                              detail: { message: 'Update failed. Please try again.', type: 'error' }
                                          }));
                                      }
                                  });

                                  xhr.addEventListener('error', () => {
                                      this.uploading = false;
                                      window.dispatchEvent(new CustomEvent('toast', {
                                          detail: { message: 'Update failed. Please try again.', type: 'error' }
                                      }));
                                  });

                                  xhr.open('POST', form.action);
                                  xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name=csrf-token]').content);
                                  xhr.send(formData);
                              }
                          }"
                          @submit="submitForm($event)">
                        @csrf
                        @method('PUT')

                        <!-- Title -->
                        <div class="mb-6">
                            <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Title</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $audioFile->title) }}" required
                                   placeholder="Enter a name for this audio file"
                                   class="block w-full rounded-xl border-gray-200 focus:border-violet-500 focus:ring-violet-500 text-sm px-4 py-3 transition @error('title') border-red-300 focus:border-red-500 focus:ring-red-500 @enderror">
                            @error('title')
                                <p class="mt-1.5 text-sm text-red-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Current File -->
                        <div class="mb-6 bg-gray-50/70 rounded-xl p-5 border border-gray-100">
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-sm font-semibold text-gray-700">Current File</span>
                                <span class="text-xs text-gray-400">{{ $audioFile->filename }}</span>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-100">
                                <x-audio-player :src="asset('storage/' . $audioFile->file_path)" :id="$audioFile->id" />
                            </div>
                        </div>

                        <!-- Replace File Zone -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Replace File</label>
                            <div
                                @dragover.prevent="dragging = true"
                                @dragleave.prevent="dragging = false"
                                @drop.prevent="handleDrop($event)"
                                :class="dragging ? 'border-violet-500 bg-violet-50/50' : 'border-gray-200 bg-gray-50/50'"
                                class="relative border-2 border-dashed rounded-xl p-8 text-center transition cursor-pointer hover:border-gray-300"
                                @click="document.getElementById('fileInput').click()">

                                <template x-if="!newFile">
                                    <div>
                                        <div class="mx-auto w-12 h-12 rounded-full bg-violet-50 flex items-center justify-center mb-3">
                                            <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-700">Drop a new MP3 file here or click to replace</p>
                                        <p class="text-xs text-gray-400 mt-1">Maximum file size: 64MB</p>
                                    </div>
                                </template>

                                <template x-if="newFile">
                                    <div class="flex items-center justify-center gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-violet-100 flex items-center justify-center shrink-0">
                                            <svg class="w-5 h-5 text-violet-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.369 4.369 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z"/>
                                            </svg>
                                        </div>
                                        <div class="text-left">
                                            <p class="text-sm font-medium text-gray-900" x-text="newFileName"></p>
                                            <p class="text-xs text-gray-500" x-text="newFileSize"></p>
                                        </div>
                                        <button type="button" @click.stop="removeFile()"
                                                class="ml-2 text-gray-400 hover:text-red-500 transition">
                                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                            </svg>
                                        </button>
                                    </div>
                                </template>

                                <input type="file" name="file" id="fileInput" accept=".mp3,audio/mpeg"
                                       class="hidden" @change="handleFileSelect($event)">
                            </div>
                            @error('file')
                                <p class="mt-1.5 text-sm text-red-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Progress Bar -->
                        <div x-show="uploading" x-transition class="mb-6">
                            <div class="flex justify-between text-sm mb-1.5">
                                <span class="font-medium text-gray-700">Updating...</span>
                                <span class="font-medium text-violet-600" x-text="progress + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5 overflow-hidden">
                                <div class="bg-gradient-to-r from-violet-600 to-fuchsia-500 h-2.5 rounded-full transition-all duration-200"
                                     :style="'width: ' + progress + '%'"></div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit" :disabled="uploading"
                                    class="inline-flex items-center px-6 py-2.5 bg-gradient-to-r from-violet-600 to-fuchsia-600 border border-transparent rounded-xl font-semibold text-sm text-white hover:from-violet-700 hover:to-fuchsia-700 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg shadow-violet-500/25">
                                <template x-if="!uploading">
                                    <span>Update File</span>
                                </template>
                                <template x-if="uploading">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Updating...
                                    </span>
                                </template>
                            </button>
                            <a href="{{ route('admin.audio-files.index') }}"
                               class="inline-flex items-center px-6 py-2.5 bg-white border border-gray-200 rounded-lg font-semibold text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
