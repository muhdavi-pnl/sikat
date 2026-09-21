@php
    $selectedGender = old('jenis_kelamin', !is_null($pegawai->jenis_kelamin) ? (string) (int) $pegawai->jenis_kelamin : '');

    $selectedJabatanId = old('jabatan_id', $pegawai->jabatan_id);
    $selectedJabatanText = old('jabatan_id_text', $pegawai->jabatan ? trim($pegawai->jabatan->jabatan . ($pegawai->jabatan->kode_jabatan ? ' (' . $pegawai->jabatan->kode_jabatan . ')' : '')) : '');

    $selectedJabatanRangkapId = old('jabatan_rangkap_id', $pegawai->jabatan_rangkap_id ?? $pegawai->jabatan_struktural_id);
    $selectedJabatanRangkapText = old('jabatan_rangkap_id_text', $pegawai->jabatan_rangkap ? trim($pegawai->jabatan_rangkap->jabatan . ($pegawai->jabatan_rangkap->kode_jabatan ? ' (' . $pegawai->jabatan_rangkap->kode_jabatan . ')' : '')) : '');

    $selectedProvinsiAsalId = (string) old('provinsi_asal_id', $selectedProvinsiAsalId ?? '');
    $selectedKabupatenAsalId = (string) old('kabupaten_asal_id', $selectedKabupatenAsalId ?? '');
    $selectedKecamatanAsalId = (string) old('kecamatan_asal_id', $selectedKecamatanAsalId ?? '');
    $selectedKelurahanAsalId = (string) old('kelurahan_asal_id', $selectedKelurahanAsalId ?? '');

    $selectedProvinsiId = (string) old('provinsi_id', $selectedProvinsiId ?? '');
    $selectedKabupatenId = (string) old('kabupaten_id', $selectedKabupatenId ?? '');
    $selectedKecamatanId = (string) old('kecamatan_id', $selectedKecamatanId ?? '');
    $selectedKelurahanId = (string) old('kelurahan_id', $selectedKelurahanId ?? '');

    $isAlamatSamaChecked = old('alamat_sama', $isAlamatSama ?? false);

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
                            <label>BPJS</label>
                            <input type="text" name="bpjs" class="form-control" value="{{ old('bpjs', $pegawai->bpjs) }}">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>NPWP</label>
                            <input type="text" name="npwp" class="form-control" value="{{ old('npwp', $pegawai->npwp) }}">
                        </div>
                        <div class="form-group col-md-6">
                            <label>Tanggal Lulus</label>
                            <input type="date" name="tanggal_lulus" class="form-control" value="{{ old('tanggal_lulus', optional($pegawai->tanggal_lulus)->format('Y-m-d')) }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pendidikan Terakhir</label>
                            <select name="pendidikan_id" class="form-control form-select2" data-placeholder="-- Pilih Pendidikan --">
                                <option value=""></option>
                                @foreach($pendidikans as $pendidikan)
                                    <option value="{{ $pendidikan->id }}" {{ (string) old('pendidikan_id', $pegawai->pendidikan_id) === (string) $pendidikan->id ? 'selected' : '' }}>
                                        {{ $pendidikan->pendidikan }}{{ $pendidikan->perguruan_tinggi ? ' - ' . $pendidikan->perguruan_tinggi->perguruan_tinggi : '' }}
                                    </option>
                                @endforeach
                            </select>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data Kontak & Alamat</h4>
                </div>
                <div class="card-body">
                    {{-- Alamat Asal --}}
                    <div class="border rounded p-3 mb-3" data-region-group="asal">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0 text-primary"><i class="fas fa-home mr-1"></i> Alamat Asal (KTP)</h5>
                            <small class="text-muted">Alamat sesuai KTP / daerah asal.</small>
                        </div>
                        <div class="form-group">
                            <label>Alamat Asal</label>
                            <textarea name="alamat_asal" class="form-control" rows="3" placeholder="Contoh: Jalan Merdeka No. 10, RT 01 / RW 02">{{ old('alamat_asal', $pegawai->alamat_asal) }}</textarea>
                            <small class="text-muted d-block mt-1">Lengkapi pilihan provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan asal.</small>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Provinsi Asal</label>
                                <select name="provinsi_asal_id" class="form-control form-select2-domisili" data-domisili="provinsi" data-placeholder="-- Pilih Provinsi --">
                                    <option value="">-- Pilih Provinsi --</option>
                                    @foreach($provinsis as $provinsi)
                                        <option value="{{ $provinsi->id }}" {{ $selectedProvinsiAsalId === (string) $provinsi->id ? 'selected' : '' }}>{{ $provinsi->provinsi }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Kabupaten / Kota Asal</label>
                                <select name="kabupaten_asal_id" class="form-control form-select2-domisili" data-domisili="kabupaten" data-placeholder="-- Pilih Kabupaten/Kota --" {{ $selectedProvinsiAsalId === '' ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Kabupaten/Kota --</option>
                                    @foreach($kabupatensAsal as $kabupaten)
                                        <option value="{{ $kabupaten->id }}" {{ $selectedKabupatenAsalId === (string) $kabupaten->id ? 'selected' : '' }}>{{ $kabupaten->kabupaten }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Kecamatan Asal</label>
                                <select name="kecamatan_asal_id" class="form-control form-select2-domisili" data-domisili="kecamatan" data-placeholder="-- Pilih Kecamatan --" {{ $selectedKabupatenAsalId === '' ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Kecamatan --</option>
                                    @foreach($kecamatansAsal as $kecamatan)
                                        <option value="{{ $kecamatan->id }}" {{ $selectedKecamatanAsalId === (string) $kecamatan->id ? 'selected' : '' }}>{{ $kecamatan->kecamatan }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Desa / Kelurahan Asal</label>
                                <select name="kelurahan_asal_id" class="form-control form-select2-domisili" data-domisili="kelurahan" data-placeholder="-- Pilih Desa / Kelurahan --" {{ $selectedKecamatanAsalId === '' ? 'disabled' : '' }}>
                                    <option value="">-- Pilih Desa / Kelurahan --</option>
                                    @foreach($kelurahansAsal as $kelurahan)
                                        <option value="{{ $kelurahan->id }}" {{ $selectedKelurahanAsalId === (string) $kelurahan->id ? 'selected' : '' }}>{{ $kelurahan->desa }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                    <div class="form-group mb-0">
                        <label>Nomor Telepon</label>
                        <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $pegawai->no_telp) }}">
                    </div>

                    {{-- Checkbox Sinkronisasi Alamat --}}
                    <div class="my-3 p-3 bg-light border rounded">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="alamat_sama" name="alamat_sama" value="1" data-alamat-sync {{ $isAlamatSamaChecked ? 'checked' : '' }}>
                            <label class="custom-control-label font-weight-bold" for="alamat_sama">
                                <i class="fas fa-link text-info mr-1"></i> Alamat domisili sama dengan alamat asal
                            </label>
                            <small class="text-muted d-block mt-1">Centang jika tempat tinggal saat ini (domisili) sama dengan alamat asal. Kolom domisili akan otomatis tersinkronisasi.</small>
                        </div>
                    </div>

                    {{-- Alamat Domisili --}}
                    <div class="border rounded p-3 mb-3" data-region-group="domisili">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h5 class="mb-0 text-success"><i class="fas fa-map-marker-alt mr-1"></i> Alamat Domisili</h5>
                            <span class="badge badge-info sync-indicator {{ $isAlamatSamaChecked ? '' : 'd-none' }}"><i class="fas fa-link mr-1"></i> Tersinkron dengan Alamat Asal</span>
                        </div>
                        <div class="form-group">
                            <label>Alamat Domisili</label>
                            <textarea name="alamat" class="form-control" rows="3" placeholder="Contoh: Jalan Merdeka No. 10">{{ old('alamat', $pegawai->alamat) }}</textarea>
                            <small class="text-muted d-block mt-1">Saat mengisi alamat domisili terpisah, lengkapi pilihan provinsi, kabupaten/kota, kecamatan, dan desa/kelurahan.</small>
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
                            <label>Kelompok Pegawai</label>
                            <select name="kelompok_pegawai" class="form-control form-select2" data-placeholder="-- Pilih Kelompok Pegawai --">
                                <option value="dosen" {{ old('kelompok_pegawai', $pegawai->kelompok_pegawai ?: 'dosen') === 'dosen' ? 'selected' : '' }}>Dosen</option>
                                <option value="tendik" {{ in_array(old('kelompok_pegawai', $pegawai->kelompok_pegawai), ['tendik', 'tenaga kependidikan']) ? 'selected' : '' }}>Tenaga Kependidikan</option>
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
                            <label>Pangkat / Golongan</label>
                            <select name="pangkat_id" class="form-control form-select2" data-placeholder="-- Pilih Pangkat --">
                                <option value=""></option>
                                @foreach($pangkats as $pangkat)
                                    <option value="{{ $pangkat->id }}" {{ (string) old('pangkat_id', $pegawai->pangkat_id) === (string) $pangkat->id ? 'selected' : '' }}>{{ $pangkat->pangkat }} - {{ $pangkat->golongan_ruang }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-6">
                            <label>TMT Pangkat</label>
                            <input type="date" name="tmt_pangkat" class="form-control" value="{{ old('tmt_pangkat', optional($pegawai->tmt_pangkat)->format('Y-m-d')) }}">
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
                            <label>Jenis Jabatan</label>
                            <select name="jenis_jabatan_id" class="form-control form-select2" data-placeholder="-- Pilih Jenis Jabatan --">
                                <option value=""></option>
                                @foreach($jenisJabatans as $jenisJabatan)
                                    <option value="{{ $jenisJabatan->id }}" {{ (string) old('jenis_jabatan_id', $pegawai->jenis_jabatan_id) === (string) $jenisJabatan->id ? 'selected' : '' }}>{{ $jenisJabatan->jenis_jabatan }}</option>
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
                        <div class="form-group col-md-6" id="wrapper-jabatan-rangkap" style="{{ (string) old('jenis_jabatan_id', $pegawai->jenis_jabatan_id) === '3' ? '' : 'display: none;' }}">
                            <label>Jabatan Rangkap</label>
                            <select
                                name="jabatan_rangkap_id"
                                class="form-control form-select2-ajax"
                                data-placeholder="-- Pilih Jabatan Rangkap --"
                                data-url="{{ route('kepegawaian.pegawai.options.jabatans') }}"
                                data-text-target="jabatan_rangkap_id_text"
                            >
                                <option value=""></option>
                                @if($selectedJabatanRangkapId && $selectedJabatanRangkapText)
                                    <option value="{{ $selectedJabatanRangkapId }}" selected>{{ $selectedJabatanRangkapText }}</option>
                                @endif
                            </select>
                            <input type="hidden" name="jabatan_rangkap_id_text" value="{{ $selectedJabatanRangkapText }}">
                            <small class="text-muted d-block mt-1">Hanya berlaku jika jenis jabatan adalah Jabatan Rangkap (Struktural dan Fungsional).</small>
                        </div>
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

                    {{-- Peninjauan Masa Kerja (PMK) --}}
                    <div class="border rounded p-3 mb-3 bg-light">
                        <h6 class="text-dark font-weight-bold mb-2"><i class="fas fa-history mr-1"></i> Peninjauan Masa Kerja (PMK)</h6>
                        <div class="form-row">
                            <div class="form-group col-md-4 mb-md-0">
                                <label>TMT PMK</label>
                                <input type="date" name="tmt_pmk" class="form-control" value="{{ old('tmt_pmk', optional($pegawai->tmt_pmk)->format('Y-m-d')) }}">
                            </div>
                            <div class="form-group col-md-4 mb-md-0">
                                <label>Masa Kerja (Tahun)</label>
                                <input type="number" min="0" max="50" name="pmk_tahun" class="form-control" placeholder="0" value="{{ old('pmk_tahun', $pegawai->pmk_tahun) }}">
                            </div>
                            <div class="form-group col-md-4 mb-0">
                                <label>Masa Kerja (Bulan)</label>
                                <input type="number" min="0" max="11" name="pmk_bulan" class="form-control" placeholder="0" value="{{ old('pmk_bulan', $pegawai->pmk_bulan) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Jatah Cuti 3 Tahun --}}
                    <div class="border rounded p-3 mb-0 bg-light">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="text-dark font-weight-bold mb-0">
                                <i class="fas fa-umbrella-beach text-primary mr-1"></i> Jatah Cuti Tahunan (3 Tahun Terakhir)
                            </h6>
                            <small class="badge badge-info">Tahun {{ now()->year - 2 }} s/d {{ now()->year }}</small>
                        </div>
                        <p class="text-muted text-small mb-3">
                            Tentukan jatah cuti per tahun yang disimpan di database untuk pegawai ini. Jatah cuti tahun berjalan (N) maksimal 12 hari, sedangkan tahun N-1 dan N-2 maksimal 6 hari.
                        </p>
                        <div class="form-row">
                            @php
                                $formCurrentYear = now()->year;
                            @endphp
                            @for($i = 2; $i >= 0; $i--)
                                @php
                                    $year = $formCurrentYear - $i;
                                    $maxDays = $i === 0 ? 12 : 6;
                                    $defaultVal = $i === 0 ? 12 : 6;
                                    $quotaValue = old("cuti_quotas.{$year}", $pegawai->id ? $pegawai->getCutiQuotaForYear($year) : $defaultVal);
                                @endphp
                                <div class="form-group col-md-4 mb-md-0">
                                    <label class="font-weight-bold">
                                        Tahun {{ $year }}
                                        @if($i === 0)
                                            <span class="badge badge-primary badge-sm ml-1">N</span>
                                        @else
                                            <span class="badge badge-secondary badge-sm ml-1">N-{{ $i }}</span>
                                        @endif
                                    </label>
                                    <div class="input-group">
                                        <input
                                            type="number"
                                            min="0"
                                            max="{{ $maxDays }}"
                                            name="cuti_quotas[{{ $year }}]"
                                            class="form-control @error('cuti_quotas.' . $year) is-invalid @enderror"
                                            value="{{ $quotaValue }}"
                                            placeholder="{{ $defaultVal }}"
                                            required
                                        >
                                        <div class="input-group-append">
                                            <span class="input-group-text">Hari</span>
                                        </div>
                                    </div>
                                    @error('cuti_quotas.' . $year)
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endfor
                        </div>
                        <input type="hidden" name="cuti_hari_tersedia" value="{{ old('cuti_hari_tersedia', $pegawai->cuti_hari_tersedia ?? 12) }}">
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h4>Data Akun, Homebase & Akademik</h4>
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

                    {{-- Khusus Dosen: Kelompok Keahlian, Bidang Penelitian & Identitas Akademik --}}
                    @php
                        $isTendik = in_array(old('kelompok_pegawai', $pegawai->kelompok_pegawai), ['tendik', 'tenaga kependidikan'], true);
                    @endphp
                    <div id="section-akademik-dosen" style="{{ $isTendik ? 'display: none;' : '' }}">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Kelompok Keahlian</label>
                                <select name="kelompok_keahlian_id" class="form-control form-select2" data-placeholder="-- Pilih Kelompok Keahlian --">
                                    <option value=""></option>
                                    @foreach($kelompokKeahlians as $kelompokKeahlian)
                                        <option value="{{ $kelompokKeahlian->id }}" {{ (string) old('kelompok_keahlian_id', $pegawai->kelompok_keahlian_id) === (string) $kelompokKeahlian->id ? 'selected' : '' }}>{{ $kelompokKeahlian->nama_kelompok }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Bidang Penelitian</label>
                                <input type="text" name="bidang_penelitian" class="form-control" placeholder="Contoh: Kecerdasan Buatan" value="{{ old('bidang_penelitian', $pegawai->bidang_penelitian) }}">
                            </div>
                        </div>

                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0">Identitas Akademik & Peneliti</h5>
                                <small class="text-muted">Isi ID profil akademik & penelitian bila tersedia.</small>
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
                            <div class="form-row">
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
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>NUPTK</label>
                                    <input type="text" name="nuptk" class="form-control" value="{{ old('nuptk', $pegawai->nuptk) }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>NIDN</label>
                                    <input type="text" name="nidn" class="form-control" value="{{ old('nidn', $pegawai->nidn) }}">
                                </div>
                                <div class="form-group col-md-4">
                                    <label>No. Serdos</label>
                                    <input type="text" name="no_serdos" class="form-control" placeholder="No. Sertifikat Pendidik" value="{{ old('no_serdos', $pegawai->no_serdos) }}">
                                </div>
                            </div>
                        </div>
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


