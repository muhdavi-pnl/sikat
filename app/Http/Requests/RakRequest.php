<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RakRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'lemari_id' => ['required', 'exists:lemaris,id'],
            'rak' => ['required', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:100'],
        ];
    }
}

