<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LemariRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'ruang_id' => ['required', 'exists:ruangs,id'],
            'lemari' => ['required', 'string', 'max:75'],
            'keterangan' => ['nullable', 'string', 'max:100'],
        ];
    }
}

