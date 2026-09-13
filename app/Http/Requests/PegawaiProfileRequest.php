<?php

namespace App\Http\Requests;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Pegawai;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PegawaiProfileRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $pegawaiId = optional(auth()->user()->pegawai)->id;

        return [
            'id_wos' => ['nullable', 'string', 'max:15', Rule::unique('pegawai_identitas', 'id_wos')->ignore($pegawaiId, 'pegawai_id')],
            'id_orc' => ['nullable', 'string', 'max:20', Rule::unique('pegawai_identitas', 'id_orc')->ignore($pegawaiId, 'pegawai_id')],
            'id_sinta' => ['nullable', 'string', 'max:10', Rule::unique('pegawai_identitas', 'id_sinta')->ignore($pegawaiId, 'pegawai_id')],
            'id_scopus' => ['nullable', 'string', 'max:15', Rule::unique('pegawai_identitas', 'id_scopus')->ignore($pegawaiId, 'pegawai_id')],
            'id_garuda' => ['nullable', 'string', 'max:10', Rule::unique('pegawai_identitas', 'id_garuda')->ignore($pegawaiId, 'pegawai_id')],
            'nidn' => ['nullable', 'string', 'max:10', Rule::unique('pegawai_identitas', 'nidn')->ignore($pegawaiId, 'pegawai_id')],
            'nuptk' => ['nullable', 'string', 'max:16', Rule::unique('pegawai_identitas', 'nuptk')->ignore($pegawaiId, 'pegawai_id')],
            'nip' => ['required', 'string', 'max:18', Rule::unique('pegawais', 'nip')->ignore($pegawaiId)],
            'nama' => ['required', 'string', 'max:150'],
            'id_gscholar' => ['nullable', 'string', 'max:15', Rule::unique('pegawai_identitas', 'id_gscholar')->ignore($pegawaiId, 'pegawai_id')],
            'nik' => ['nullable', 'string', 'max:16', Rule::unique('pegawais', 'nik')->ignore($pegawaiId)],
            'tempat_lahir' => ['nullable', 'string', 'max:50'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'boolean'],
            'email' => ['nullable', 'email', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'no_telp' => ['nullable', 'string', 'max:15'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'provinsi_id' => ['nullable', 'exists:provinsis,id'],
            'kabupaten_id' => ['nullable', 'exists:kabupatens,id'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'kelurahan_id' => ['nullable', 'exists:kelurahans,id'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
            'program_studi_id' => ['nullable', 'exists:program_studis,id'],
            'jabatan_fungsional' => ['nullable', Rule::in(Pegawai::jabatanFungsionalValidationValues())],
        ];
    }

    public function attributes()
    {
        return [
            'alamat' => 'alamat domisili',
            'provinsi_id' => 'provinsi',
            'kabupaten_id' => 'kabupaten/kota',
            'kecamatan_id' => 'kecamatan',
            'kelurahan_id' => 'desa/kelurahan',
            'no_hp' => 'nomor HP',
            'no_telp' => 'nomor telepon',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateDomisiliHierarchy($validator);
        });
    }

    protected function validateDomisiliHierarchy($validator): void
    {
        if (!$this->hasAnyDomisiliInput()) {
            return;
        }

        foreach (['alamat', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id'] as $field) {
            if (!$this->filled($field)) {
                $validator->errors()->add($field, 'Kolom ' . $this->attributes()[$field] . ' wajib diisi saat melengkapi domisili.');
            }
        }

        if (!$this->filled('provinsi_id') || !$this->filled('kabupaten_id') || !$this->filled('kecamatan_id') || !$this->filled('kelurahan_id')) {
            return;
        }

        $kabupaten = Kabupaten::find($this->input('kabupaten_id'));
        if ($kabupaten && (string) $kabupaten->provinsi_id !== (string) $this->input('provinsi_id')) {
            $validator->errors()->add('kabupaten_id', 'Kabupaten/Kota tidak sesuai dengan provinsi yang dipilih.');
        }

        $kecamatan = Kecamatan::find($this->input('kecamatan_id'));
        if ($kecamatan && (string) $kecamatan->kabupaten_id !== (string) $this->input('kabupaten_id')) {
            $validator->errors()->add('kecamatan_id', 'Kecamatan tidak sesuai dengan kabupaten/kota yang dipilih.');
        }

        $kelurahan = Kelurahan::find($this->input('kelurahan_id'));
        if ($kelurahan && (string) $kelurahan->kecamatan_id !== (string) $this->input('kecamatan_id')) {
            $validator->errors()->add('kelurahan_id', 'Desa/kelurahan tidak sesuai dengan kecamatan yang dipilih.');
        }
    }

    protected function hasAnyDomisiliInput(): bool
    {
        foreach (['alamat', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'kelurahan_id'] as $field) {
            $value = $this->input($field);

            if ($value !== null && $value !== '') {
                return true;
            }
        }

        return false;
    }
}

