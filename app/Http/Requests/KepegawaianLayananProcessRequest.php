<?php

namespace App\Http\Requests;

use App\Models\LayananPegawai;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KepegawaianLayananProcessRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'layanan_id' => ['required', 'exists:layanans,id'],
            'status' => ['required', Rule::in(array_keys(LayananPegawai::statusOptions()))],
            'catatan_proses' => ['nullable', 'string', 'max:1000'],
            'output_file' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:5120'],
            'syarat_reviews' => ['nullable', 'array'],
            'syarat_reviews.*.status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
            'syarat_reviews.*.catatan' => ['nullable', 'string', 'max:500'],
        ];
    }
}

