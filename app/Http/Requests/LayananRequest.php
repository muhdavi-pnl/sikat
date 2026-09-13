<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LayananRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'layanan' => ['required', 'string', 'max:150'],
            'deskripsi' => ['nullable', 'string'],
            'jenis' => ['required', Rule::in(['kepegawaian', 'fungsional'])],
            'syarat_ids' => ['nullable', 'array'],
            'syarat_ids.*' => ['integer', 'exists:syarats,id'],
        ];
    }

    public function attributes()
    {
        return [
            'layanan' => 'nama layanan',
            'deskripsi' => 'deskripsi layanan',
            'jenis' => 'jenis layanan',
            'syarat_ids' => 'persyaratan',
            'syarat_ids.*' => 'persyaratan',
        ];
    }
}

