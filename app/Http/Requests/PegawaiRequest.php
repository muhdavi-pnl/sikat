<?php

namespace App\Http\Requests;

use App\Models\Kabupaten;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Pegawai;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PegawaiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return Gate::allows('manage-pegawai');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $pegawai = $this->route('pegawai');
        $pegawaiId = $pegawai instanceof Pegawai ? $pegawai->id : $pegawai;

        return [
            'id_wos' => ['nullable', 'string', 'max:15', Rule::unique('pegawai_identitas', 'id_wos')->ignore($pegawaiId, 'pegawai_id')],
            'id_orc' => ['nullable', 'string', 'max:20', Rule::unique('pegawai_identitas', 'id_orc')->ignore($pegawaiId, 'pegawai_id')],
            'id_sinta' => ['nullable', 'string', 'max:10', Rule::unique('pegawai_identitas', 'id_sinta')->ignore($pegawaiId, 'pegawai_id')],
            'id_scopus' => ['nullable', 'string', 'max:15', Rule::unique('pegawai_identitas', 'id_scopus')->ignore($pegawaiId, 'pegawai_id')],
            'id_garuda' => ['nullable', 'string', 'max:10', Rule::unique('pegawai_identitas', 'id_garuda')->ignore($pegawaiId, 'pegawai_id')],
            'id_gscholar' => ['nullable', 'string', 'max:15', Rule::unique('pegawai_identitas', 'id_gscholar')->ignore($pegawaiId, 'pegawai_id')],
            'nidn' => ['nullable', 'string', 'max:10', Rule::unique('pegawai_identitas', 'nidn')->ignore($pegawaiId, 'pegawai_id')],
            'nuptk' => ['nullable', 'string', 'max:16', Rule::unique('pegawai_identitas', 'nuptk')->ignore($pegawaiId, 'pegawai_id')],
            'nip' => ['required', 'string', 'max:18', Rule::unique('pegawais', 'nip')->ignore($pegawaiId)],
            'nik' => ['nullable', 'string', 'max:16', Rule::unique('pegawais', 'nik')->ignore($pegawaiId)],
            'nama' => ['required', 'string', 'max:150'],
            'gelar_depan' => ['nullable', 'string', 'max:25'],
            'gelar_belakang' => ['nullable', 'string', 'max:30'],
            'tempat_lahir' => ['nullable', 'string', 'max:50'],
            'tanggal_lahir' => ['nullable', 'date'],
            'jenis_kelamin' => ['nullable', 'boolean'],
            'jumlah_anak' => ['nullable', 'integer', 'min:0'],
            'tanggal_lulus' => ['nullable', 'date'],
            'npwp' => ['nullable', 'string', 'max:25'],
            'bpjs' => ['nullable', 'string', 'max:20'],
            'no_karpeg' => ['nullable', 'string', 'max:25'],
            'no_karis_karsu' => ['nullable', 'string', 'max:25'],
            'tmt_cpns' => ['nullable', 'date'],
            'tmt_pns' => ['nullable', 'date'],
            'tmt_jabatan' => ['nullable', 'date'],
            'email' => ['nullable', 'email', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:15'],
            'no_telp' => ['nullable', 'string', 'max:15'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'provinsi_id' => ['nullable', 'exists:provinsis,id'],
            'kabupaten_id' => ['nullable', 'exists:kabupatens,id'],
            'kecamatan_id' => ['nullable', 'exists:kecamatans,id'],
            'kelurahan_id' => ['nullable', 'exists:kelurahans,id'],
            'eselon_id' => ['nullable', 'exists:eselons,id'],
            'kedudukan_pegawai_id' => ['nullable', 'exists:kedudukan_pegawais,id'],
            'agama_id' => ['nullable', 'exists:agamas,id'],
            'jabatan_id' => ['nullable', 'exists:jabatans,id'],
            'pangkat_id' => ['nullable', 'exists:pangkats,id'],
            'status_perkawinan_id' => ['nullable', 'exists:status_perkawinans,id'],
            'pendidikan_id' => ['nullable', 'exists:pendidikans,id'],
            'program_studi_id' => ['nullable', 'exists:program_studis,id'],
            'unit_kerja_id' => ['nullable', 'exists:unit_kerjas,id'],
            'user_id' => ['nullable', 'exists:users,id', Rule::unique('pegawais', 'user_id')->ignore($pegawaiId)],
            'jabatan_fungsional' => ['nullable', Rule::in(Pegawai::jabatanFungsionalValidationValues())],
            'status_pegawai' => ['nullable', Rule::in(['CPNS', 'PNS', 'PPPK'])],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'id_wos' => 'ID WOS Researcher',
            'id_orc' => 'ID ORCID',
            'id_sinta' => 'ID SINTA',
            'id_scopus' => 'ID Scopus',
            'id_garuda' => 'ID Garuda',
            'id_gscholar' => 'ID Google Scholar',
            'nidn' => 'NIDN',
            'nuptk' => 'NUPTK',
            'nip' => 'NIP',
            'nik' => 'NIK',
            'nama' => 'nama',
            'gelar_depan' => 'gelar depan',
            'gelar_belakang' => 'gelar belakang',
            'tempat_lahir' => 'tempat lahir',
            'tanggal_lahir' => 'tanggal lahir',
            'jenis_kelamin' => 'jenis kelamin',
            'jumlah_anak' => 'jumlah anak',
            'tanggal_lulus' => 'tanggal lulus',
            'npwp' => 'NPWP',
            'bpjs' => 'BPJS',
            'no_karpeg' => 'nomor KARPEG',
            'no_karis_karsu' => 'nomor KARIS/KARSU',
            'tmt_cpns' => 'TMT CPNS',
            'tmt_pns' => 'TMT PNS',
            'tmt_jabatan' => 'TMT jabatan',
            'alamat' => 'alamat domisili',
            'no_hp' => 'nomor HP',
            'no_telp' => 'nomor telepon',
            'provinsi_id' => 'provinsi',
            'kabupaten_id' => 'kabupaten/kota',
            'kecamatan_id' => 'kecamatan',
            'kelurahan_id' => 'desa/kelurahan',
            'eselon_id' => 'eselon',
            'kedudukan_pegawai_id' => 'kedudukan pegawai',
            'agama_id' => 'agama',
            'jabatan_id' => 'jabatan',
            'pangkat_id' => 'pangkat',
            'status_perkawinan_id' => 'status perkawinan',
            'pendidikan_id' => 'pendidikan',
            'program_studi_id' => 'program studi',
            'unit_kerja_id' => 'unit kerja',
            'user_id' => 'akun pengguna',
            'jabatan_fungsional' => 'jabatan fungsional',
            'status_pegawai' => 'status pegawai',
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

