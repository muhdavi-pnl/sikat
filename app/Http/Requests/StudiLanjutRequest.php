<?php

namespace App\Http\Requests;

use App\Models\StudiLanjut;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudiLanjutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'pegawai_id' => ['required', 'exists:pegawais,id'],
            'progres' => ['required', Rule::in(array_keys(StudiLanjut::PROGRES_OPTIONS))],
            'jenis_pembiayaan' => ['required', Rule::in(array_keys(StudiLanjut::JENIS_PEMBIAYAAN_OPTIONS))],
            'nama_beasiswa' => ['nullable', 'string', 'max:255'],
            'jenis_tugas' => ['required', Rule::in(array_keys(StudiLanjut::JENIS_TUGAS_OPTIONS))],
            'bidang_ilmu' => ['required', Rule::in(array_keys(StudiLanjut::BIDANG_ILMU_OPTIONS))],
            'jenjang' => ['nullable', 'string', 'max:50'],
            'program_studi' => ['required', 'string', 'max:255'],
            'nama_institusi' => ['required', 'string', 'max:255'],
            'negara' => ['nullable', 'string', 'max:100'],
            'tanggal_mulai' => ['nullable', 'date'],
            'target_selesai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date'],
            'nomor_sk' => ['nullable', 'string', 'max:255'],
            'tanggal_sk' => ['nullable', 'date'],
            'dokumen_sk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'keterangan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'pegawai_id.required' => 'Pegawai wajib dipilih.',
            'pegawai_id.exists' => 'Pegawai yang dipilih tidak valid.',
            'progres.required' => 'Progres studi wajib dipilih.',
            'progres.in' => 'Progres studi tidak valid (pilih defer, ongoing, atau selesai).',
            'jenis_pembiayaan.required' => 'Jenis pembiayaan wajib dipilih.',
            'jenis_pembiayaan.in' => 'Jenis pembiayaan harus berupa Mandiri atau Beasiswa.',
            'jenis_tugas.required' => 'Jenis tugas belajar wajib dipilih.',
            'jenis_tugas.in' => 'Jenis tugas harus berupa Meninggalkan Tugas atau Menjalankan Tugas.',
            'bidang_ilmu.required' => 'Bidang ilmu wajib dipilih.',
            'bidang_ilmu.in' => 'Bidang ilmu harus berupa STEM, EKONOMI, SOSIAL, HUMANIORA, atau KEAGAMAAN.',
            'program_studi.required' => 'Program studi wajib diisi.',
            'nama_institusi.required' => 'Nama institusi/perguruan tinggi wajib diisi.',
            'dokumen_sk.mimes' => 'File dokumen SK harus berformat PDF, JPG, JPEG, atau PNG.',
            'dokumen_sk.max' => 'Ukuran file dokumen SK maksimal adalah 10MB.',
        ];
    }
}
