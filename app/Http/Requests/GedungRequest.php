<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GedungRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $gedung = $this->route('gedung');

        return [
            'nama_gedung' => [
                'required',
                'string',
                'max:255',
                Rule::unique('gedungs', 'nama_gedung')->ignore(optional($gedung)->id),
            ],
            'alamat_gedung' => ['nullable', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:100'],
        ];
    }
}

