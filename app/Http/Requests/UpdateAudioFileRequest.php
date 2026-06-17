<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAudioFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'file', 'mimetypes:audio/mpeg,audio/mp3', 'mimes:mp3', 'max:65536'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'The audio file must not exceed 64MB.',
            'file.mimes' => 'The file must be an MP3 audio file.',
        ];
    }
}
