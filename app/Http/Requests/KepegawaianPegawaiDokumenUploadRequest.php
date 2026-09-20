<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KepegawaianPegawaiDokumenUploadRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super-admin', 'kepegawaian']);
    }

    public function rules()
    {
        return [
            'dokumen_id' => ['required', 'exists:dokumens,id'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'nomor' => ['nullable', 'string', 'max:100'],
            'tanggal' => ['nullable', 'date'],
            'status' => ['required', 'integer', 'in:0,1,2'],
            'alasan_penolakan' => ['nullable', 'required_if:status,2', 'string', 'max:500'],
            'keterangan' => ['nullable', 'string', 'max:150'],
        ];
    }

    public function messages()
    {
        return [
            'alasan_penolakan.required_if' => 'Alasan penolakan wajib diisi apabila status dokumen ditolak.',
        ];
    }

    public function attributes()
    {
        return [
            'dokumen_id' => 'jenis dokumen',
            'file' => 'file dokumen',
            'nomor' => 'nomor dokumen',
            'tanggal' => 'tanggal dokumen',
            'status' => 'status dokumen',
            'alasan_penolakan' => 'alasan penolakan',
            'keterangan' => 'keterangan',
        ];
    }
}

