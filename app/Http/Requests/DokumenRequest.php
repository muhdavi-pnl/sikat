<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DokumenRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $dokumen = $this->route('dokumen');

        return [
            'kode_dokumen' => [
                'required',
                'string',
                'max:15',
                Rule::unique('dokumens', 'kode_dokumen')->ignore(optional($dokumen)->id),
            ],
            'nama_dokumen' => ['required', 'string', 'max:200'],
        ];
    }
}

