<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PegawaiDokumenUploadRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'dokumen_id' => ['required', 'exists:dokumens,id'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'nomor' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['nullable', 'date'],
            'keterangan' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function attributes()
    {
        return [
            'dokumen_id' => 'jenis dokumen',
            'file' => 'file dokumen',
            'nomor' => 'nomor dokumen',
            'tanggal' => 'tanggal dokumen',
            'keterangan' => 'keterangan',
        ];
    }
}

