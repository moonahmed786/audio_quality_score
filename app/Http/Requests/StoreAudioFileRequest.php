<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAudioFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'file' => ['required', 'file', 'mimes:mp3', 'max:65536'],
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
