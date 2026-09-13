<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LokasiArsipRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'rak_id' => ['required', 'exists:raks,id'],
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'keterangan' => ['nullable', 'string', 'max:100'],
        ];
    }
}

