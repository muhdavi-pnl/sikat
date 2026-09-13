<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PegawaiLayananUsulanRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'catatan_pengusul' => ['nullable', 'string', 'max:1000'],
            'konfirmasi_kirim' => ['required', 'accepted'],
        ];
    }

    public function attributes()
    {
        return [
            'catatan_pengusul' => 'catatan usulan',
            'konfirmasi_kirim' => 'konfirmasi pengiriman usulan',
        ];
    }
}

