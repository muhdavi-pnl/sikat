@php
    $selectedGender = old('jenis_kelamin', !is_null($pegawai->jenis_kelamin) ? (string) (int) $pegawai->jenis_kelamin : '');

    $selectedJabatanId = old('jabatan_id', $pegawai->jabatan_id);
    $selectedJabatanText = old('jabatan_id_text', $pegawai->jabatan ? trim($pegawai->jabatan->jabatan . ($pegawai->jabatan->jenis_jabatan ? ' - ' . $pegawai->jabatan->jenis_jabatan->jenis_jabatan : '')) : '');

    $selectedProvinsiId = (string) old('provinsi_id', $selectedProvinsiId ?? '');
    $selectedKabupatenId = (string) old('kabupaten_id', $selectedKabupatenId ?? '');
    $selectedKecamatanId = (string) old('kecamatan_id', $selectedKecamatanId ?? '');
    $selectedKelurahanId = (string) old('kelurahan_id', $selectedKelurahanId ?? '');

    $selectedProgramStudiId = old('program_studi_id', $pegawai->program_studi_id);
    $selectedProgramStudiText = old('program_studi_id_text', $pegawai->program_studi ? trim($pegawai->program_studi->jenjang . ' - ' . $pegawai->program_studi->nama_prodi . ($pegawai->program_studi->jurusan ? ' - ' . $pegawai->program_studi->jurusan->jurusan : '')) : '');

    $selectedUserId = old('user_id', $pegawai->user_id);
    $selectedUserText = old('user_id_text', $pegawai->user ? $pegawai->user->name . ' - ' . $pegawai->user->email : '');
@endphp

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible show fade">
        <div class="alert-body">
            <button class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<form action="{{ $action }}" method="POST">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>Data Pribadi</h4>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>NIP <span class="text-danger">*</span></label>
                            <input type="text" name="nip" class="form-control" value="{{ old('nip', $pegawai->nip) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>NIK</label>
                            <input type="text" name="nik" class="form-control" value="{{ old('nik', $pegawai->nik) }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nama <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control" value="{{ old('nama', $pegawai->nama) }}" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Gelar Depan</label>
                            <input type="text" name="gelar_depan" class="form-control" value="{{ old('gelar_depan', $pegawai->gelar_depan) }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Gelar Belakang</label>
                            <input type="text" name="gelar_belakang" class="form-control" value="{{ old('gelar_belakang', $pegawai->gelar_belakang) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $pegawai->tempat_lahir) }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', optional($pegawai->tanggal_lahir)->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Jenis Kelamin</label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk-l" value="1" {{ $selectedGender === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jk-l">Laki-laki</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk-p" value="0" {{ $selectedGender === '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="jk-p">Perempuan</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Agama</label>
                            <select name="agama_id" class="form-control form-select2" data-placeholder="-- Pilih Agama --">
                                <option value=""></option>
                                @foreach($agamas as $agama)
                                    <option value="{{ $agama->id }}" {{ (string) old('agama_id', $pegawai->agama_id) === (string) $agama->id ? 'selected' : '' }}>{{ $agama->agama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status Perkawinan</label>
                            <select name="status_perkawinan_id" class="form-control form-select2" data-placeholder="-- Pilih Status --">
                                <option value=""></option>
                                @foreach($statusPerkawinans as $statusPerkawinan)
                                    <option value="{{ $statusPerkawinan->id }}" {{ (string) old('status_perkawinan_id', $pegawai->status_perkawinan_id) === (string) $statusPerkawinan->id ? 'selected' : '' }}>{{ $statusPerkawinan->status_perkawinan }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Jumlah Anak</label>
                            <input type="number" min="0" name="jumlah_anak" class="form-control" value="{{ old('jumlah_anak', $pegawai->jumlah_anak) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Pendidikan Terakhir</label>
                            <select name="pendidikan_id" class="form-control form-select2" data-placeholder="-- Pilih Pendidikan --">
                                <option value=""></option>
                                @foreach($pendidikans as $pendidikan)
                                    <option value="{{ $pendidikan->id }}" {{ (string) old('pendidikan_id', $pegawai->pendidikan_id) === (string) $pendidikan->id ? 'selected' : '' }}>{{ $pendidikan->pendidikan }} - {{ $pendidikan->perguruan_tinggi }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tanggal Lulus</label>
                            <input type="date" name="tanggal_lulus" class="form-control" value="{{ old('tanggal_lulus', optional($pegawai->tanggal_lulus)->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>NPWP</label>
                            <input type="text" name="npwp" class="form-control" value="{{ old('npwp', $pegawai->npwp) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>BPJS</label>
                        <input type="text" name="bpjs" class="form-control" value="{{ old('bpjs', $pegawai->bpjs) }}">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data Kontak</h4>
                </div>
                <div class="card-body" data-domisili-form>
                    <div class="form-group">
                        <label>Alamat Domisili</label>
                        <textarea name="alamat" class="form-control" rows="3" placeholder="Contoh: Jalan Merdeka No. 10">{{ old('alamat', $pegawai->alamat) }}</textarea>
                        <small class="text-muted d-block mt-2">Saat mengisi alamat domisili, lengkapi pilihan provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan.</small>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Provinsi</label>
                            <select name="provinsi_id" class="form-control form-select2-domisili" data-domisili="provinsi" data-placeholder="-- Pilih Provinsi --">
                                <option value="">-- Pilih Provinsi --</option>
                                @foreach($provinsis as $provinsi)
                                    <option value="{{ $provinsi->id }}" {{ $selectedProvinsiId === (string) $provinsi->id ? 'selected' : '' }}>{{ $provinsi->provinsi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Kabupaten / Kota</label>
                            <select name="kabupaten_id" class="form-control form-select2-domisili" data-domisili="kabupaten" data-placeholder="-- Pilih Kabupaten/Kota --" {{ $selectedProvinsiId === '' ? 'disabled' : '' }}>
                                <option value="">-- Pilih Kabupaten/Kota --</option>
                                @foreach($kabupatens as $kabupaten)
                                    <option value="{{ $kabupaten->id }}" {{ $selectedKabupatenId === (string) $kabupaten->id ? 'selected' : '' }}>{{ $kabupaten->kabupaten }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Kecamatan</label>
                            <select name="kecamatan_id" class="form-control form-select2-domisili" data-domisili="kecamatan" data-placeholder="-- Pilih Kecamatan --" {{ $selectedKabupatenId === '' ? 'disabled' : '' }}>
                                <option value="">-- Pilih Kecamatan --</option>
                                @foreach($kecamatans as $kecamatan)
                                    <option value="{{ $kecamatan->id }}" {{ $selectedKecamatanId === (string) $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->kecamatan }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Desa / Kelurahan</label>
                            <select name="kelurahan_id" class="form-control form-select2-domisili" data-domisili="kelurahan" data-placeholder="-- Pilih Desa / Kelurahan --" {{ $selectedKecamatanId === '' ? 'disabled' : '' }}>
                                <option value="">-- Pilih Desa / Kelurahan --</option>
                                @foreach($kelurahans as $kelurahan)
                                    <option value="{{ $kelurahan->id }}" {{ $selectedKelurahanId === (string) $kelurahan->id ? 'selected' : '' }}>{{ $kelurahan->desa }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $pegawai->email) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nomor HP</label>
                            <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $pegawai->no_hp) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $pegawai->no_telp) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4>Data Kepegawaian</h4>
                </div>
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Pangkat / Golongan</label>
                            <select name="pangkat_id" class="form-control form-select2" data-placeholder="-- Pilih Pangkat --">
                                <option value=""></option>
                                @foreach($pangkats as $pangkat)
                                    <option value="{{ $pangkat->id }}" {{ (string) old('pangkat_id', $pegawai->pangkat_id) === (string) $pangkat->id ? 'selected' : '' }}>{{ $pangkat->pangkat }} - {{ $pangkat->golongan_ruang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Status Pegawai</label>
                            <select name="status_pegawai" class="form-control form-select2" data-placeholder="-- Pilih Status Pegawai --">
                                <option value=""></option>
                                @foreach($statusPegawaiOptions as $statusPegawai)
                                    <option value="{{ $statusPegawai }}" {{ old('status_pegawai', $pegawai->status_pegawai ?: 'PNS') === $statusPegawai ? 'selected' : '' }}>{{ $statusPegawai }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>TMT CPNS</label>
                            <input type="date" name="tmt_cpns" class="form-control" value="{{ old('tmt_cpns', optional($pegawai->tmt_cpns)->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>TMT PNS</label>
                            <input type="date" name="tmt_pns" class="form-control" value="{{ old('tmt_pns', optional($pegawai->tmt_pns)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Jabatan Fungsional</label>
                            <select name="jabatan_fungsional" class="form-control form-select2" data-placeholder="-- Pilih Jabatan Fungsional --">
                                <option value=""></option>
                                @php
                                    $selectedJabatanFungsional = \App\Models\Pegawai::normalizeJabatanFungsional(old('jabatan_fungsional', $pegawai->jabatan_fungsional));
                                @endphp
                                @foreach($jabatanFungsionalOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $selectedJabatanFungsional === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Jabatan</label>
                            <select
                                name="jabatan_id"
                                class="form-control form-select2-ajax"
                                data-placeholder="-- Pilih Jabatan --"
                                data-url="{{ route('kepegawaian.pegawai.options.jabatans') }}"
                                data-text-target="jabatan_id_text"
                            >
                                <option value=""></option>
                                @if($selectedJabatanId && $selectedJabatanText)
                                    <option value="{{ $selectedJabatanId }}" selected>{{ $selectedJabatanText }}</option>
                                @endif
                            </select>
                            <input type="hidden" name="jabatan_id_text" value="{{ $selectedJabatanText }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>TMT Jabatan</label>
                            <input type="date" name="tmt_jabatan" class="form-control" value="{{ old('tmt_jabatan', optional($pegawai->tmt_jabatan)->format('Y-m-d')) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Eselon</label>
                            <select name="eselon_id" class="form-control form-select2" data-placeholder="-- Pilih Eselon --">
                                <option value=""></option>
                                @foreach($eselons as $eselon)
                                    <option value="{{ $eselon->id }}" {{ (string) old('eselon_id', $pegawai->eselon_id) === (string) $eselon->id ? 'selected' : '' }}>{{ $eselon->eselon ?: $eselon->id }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Unit Kerja</label>
                            <select name="unit_kerja_id" class="form-control form-select2" data-placeholder="-- Pilih Unit Kerja --">
                                <option value=""></option>
                                @foreach($unitKerjas as $unitKerja)
                                    <option value="{{ $unitKerja->id }}" {{ (string) old('unit_kerja_id', $pegawai->unit_kerja_id) === (string) $unitKerja->id ? 'selected' : '' }}>{{ $unitKerja->unit_kerja }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>Kedudukan Pegawai</label>
                            <select name="kedudukan_pegawai_id" class="form-control form-select2" data-placeholder="-- Pilih Kedudukan --">
                                <option value=""></option>
                                @foreach($kedudukanPegawais as $kedudukanPegawai)
                                    <option value="{{ $kedudukanPegawai->id }}" {{ (string) old('kedudukan_pegawai_id', $pegawai->kedudukan_pegawai_id) === (string) $kedudukanPegawai->id ? 'selected' : '' }}>{{ $kedudukanPegawai->kedudukan_pegawai }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Nomor KARPEG</label>
                            <input type="text" name="no_karpeg" class="form-control" value="{{ old('no_karpeg', $pegawai->no_karpeg) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Nomor KARIS / KARSU</label>
                            <input type="text" name="no_karis_karsu" class="form-control" value="{{ old('no_karis_karsu', $pegawai->no_karis_karsu) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data Akun & Homebase</h4>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Akun Pengguna</label>
                        <select
                            name="user_id"
                            class="form-control form-select2-ajax"
                            data-placeholder="-- Pilih Pengguna --"
                            data-url="{{ route('kepegawaian.pegawai.options.users') }}"
                            data-text-target="user_id_text"
                            data-current="{{ $selectedUserId }}"
                        >
                            <option value=""></option>
                            @if($selectedUserId && $selectedUserText)
                                <option value="{{ $selectedUserId }}" selected>{{ $selectedUserText }}</option>
                            @endif
                        </select>
                        <input type="hidden" name="user_id_text" value="{{ $selectedUserText }}">
                    </div>
                    <div class="form-group">
                        <label>Program Studi</label>
                        <select
                            name="program_studi_id"
                            class="form-control form-select2-ajax"
                            data-placeholder="-- Pilih Program Studi --"
                            data-url="{{ route('kepegawaian.pegawai.options.program-studis') }}"
                            data-text-target="program_studi_id_text"
                        >
                            <option value=""></option>
                            @if($selectedProgramStudiId && $selectedProgramStudiText)
                                <option value="{{ $selectedProgramStudiId }}" selected>{{ $selectedProgramStudiText }}</option>
                            @endif
                        </select>
                        <input type="hidden" name="program_studi_id_text" value="{{ $selectedProgramStudiText }}">
                    </div>
                    <div class="border rounded p-3 mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0">Identitas Akademik</h5>
                            <small class="text-muted">Isi ID profil akademik bila tersedia.</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Google Scholar</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                    </div>
                                    <input type="text" name="id_gscholar" class="form-control" value="{{ old('id_gscholar', $pegawai->id_gscholar) }}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>SINTA</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-award"></i></span>
                                    </div>
                                    <input type="text" name="id_sinta" class="form-control" value="{{ old('id_sinta', $pegawai->id_sinta) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Scopus</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-database"></i></span>
                                    </div>
                                    <input type="text" name="id_scopus" class="form-control" value="{{ old('id_scopus', $pegawai->id_scopus) }}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Garuda</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-feather-alt"></i></span>
                                    </div>
                                    <input type="text" name="id_garuda" class="form-control" value="{{ old('id_garuda', $pegawai->id_garuda) }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-row mb-0">
                            <div class="form-group col-md-6">
                                <label>WOS Researcher</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-globe"></i></span>
                                    </div>
                                    <input type="text" name="id_wos" class="form-control" value="{{ old('id_wos', $pegawai->id_wos) }}">
                                </div>
                            </div>
                            <div class="form-group col-md-6">
                                <label>ORCID</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                    </div>
                                    <input type="text" name="id_orc" class="form-control" value="{{ old('id_orc', $pegawai->id_orc) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>NUPTK</label>
                            <input type="text" name="nuptk" class="form-control" value="{{ old('nuptk', $pegawai->nuptk) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>NIDN</label>
                            <input type="text" name="nidn" class="form-control" value="{{ old('nidn', $pegawai->nidn) }}">
                        </div>
                    </div>
                    <div class="alert alert-light border mb-0 d-flex align-items-center">
                        <small class="text-muted mb-0">
                            <i class="fas fa-info-circle mr-1"></i>
                            Gunakan bagian <strong>Identitas Akademik</strong> di atas untuk mengelola profil Google Scholar, SINTA, Scopus, Garuda, WOS, dan ORCID.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body d-flex justify-content-end">
            <a href="{{ $cancelRoute }}" class="btn btn-secondary mr-2">Batal</a>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </div>
</form>


