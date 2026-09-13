<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JabatanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $jabatanId = $this->route('id') ?? optional($this->route('jabatan'))->id;

        return [
            'kode_jabatan' => ['nullable', 'string', 'max:50'],
            'jabatan' => ['required', 'string', 'max:150'],
            'jenis_jabatan_id' => ['required', 'exists:jenis_jabatans,id'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
            'atasan_langsung_id' => [
                'nullable',
                'exists:jabatans,id',
                function ($attribute, $value, $fail) use ($jabatanId) {
                    if ($jabatanId && (int) $value === (int) $jabatanId) {
                        $fail('Atasan langsung tidak boleh jabatan itu sendiri.');
                    }
                },
            ],
            'kebutuhan_pegawai' => ['nullable', 'integer', 'min:0'],
            'status_jabatan' => ['nullable', 'string', 'max:30'],
            'jenjang_jabatan' => ['nullable', 'string', 'max:100'],
            'kelas_jabatan' => ['nullable', 'integer', 'min:1', 'max:20'],
            'pangkat_golongan' => ['nullable', 'string', 'max:50'],
            'pendidikan_minimal' => ['nullable', 'string', 'max:100'],
            'kompetensi' => ['nullable', 'string'],
            'ikhtisar_jabatan' => ['nullable', 'string'],
            'uraian_tugas' => ['nullable', 'string'],
            'tanggung_jawab' => ['nullable', 'string'],
            'wewenang' => ['nullable', 'string'],
            'persyaratan_jabatan' => ['nullable', 'string'],
            'beban_kerja' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function attributes(): array
    {
        return [
            'kode_jabatan' => 'Kode Jabatan',
            'jabatan' => 'Nama Jabatan',
            'jenis_jabatan_id' => 'Jenis Jabatan',
            'unit_kerja_id' => 'Unit Kerja',
            'atasan_langsung_id' => 'Atasan Langsung',
            'kebutuhan_pegawai' => 'Kebutuhan Pegawai',
            'status_jabatan' => 'Status Jabatan',
            'jenjang_jabatan' => 'Jenjang Jabatan',
            'kelas_jabatan' => 'Kelas Jabatan',
            'pangkat_golongan' => 'Pangkat/Golongan',
            'pendidikan_minimal' => 'Pendidikan Minimal',
            'kompetensi' => 'Kompetensi',
            'ikhtisar_jabatan' => 'Ikhtisar Jabatan',
            'uraian_tugas' => 'Uraian Tugas',
            'tanggung_jawab' => 'Tanggung Jawab',
            'wewenang' => 'Wewenang',
            'persyaratan_jabatan' => 'Persyaratan Jabatan',
            'beban_kerja' => 'Beban Kerja',
        ];
    }
}
