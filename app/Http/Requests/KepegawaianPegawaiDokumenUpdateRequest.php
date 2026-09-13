<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KepegawaianPegawaiDokumenUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super-admin', 'kepegawaian']);
    }

    public function rules()
    {
        return [
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'nomor' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['nullable', 'date'],
            'status' => ['required', 'boolean'],
            'keterangan' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function attributes()
    {
        return [
            'file' => 'file dokumen',
            'nomor' => 'nomor dokumen',
            'tanggal' => 'tanggal dokumen',
            'status' => 'status dokumen',
            'keterangan' => 'keterangan',
        ];
    }
}

