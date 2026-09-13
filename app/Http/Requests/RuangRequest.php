<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RuangRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $ruang = $this->route('ruang');

        return [
            'gedung_id' => ['required', 'exists:gedungs,id'],
            'kode_ruang' => [
                'required',
                'string',
                'max:10',
                Rule::unique('ruangs', 'kode_ruang')->ignore(optional($ruang)->id),
            ],
            'nama_ruang' => ['required', 'string', 'max:75'],
            'keterangan' => ['nullable', 'string', 'max:100'],
        ];
    }
}

