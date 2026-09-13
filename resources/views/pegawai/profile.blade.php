<x-app-layout>
    @php
        $selectedProvinsiId = (string) old('provinsi_id', $selectedProvinsiId ?? '');
        $selectedKabupatenId = (string) old('kabupaten_id', $selectedKabupatenId ?? '');
        $selectedKecamatanId = (string) old('kecamatan_id', $selectedKecamatanId ?? '');
        $selectedKelurahanId = (string) old('kelurahan_id', $selectedKelurahanId ?? '');
    @endphp

    @push('plugins_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/bootstrap-social.css') }}">
    @endpush

    @push('page_css')
        <style>
            .select2-container {
                width: 100% !important;
            }
        </style>
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">Hi, {{ Auth::user()->name }}</h2>
        <p class="section-lead">Lengkapi dan perbarui data profil pegawai Anda secara mandiri.</p>

        @if(!$pegawai)
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        Data pegawai Anda belum tersedia. Silakan hubungi admin kepegawaian untuk menghubungkan akun Anda ke data pegawai.
                    </div>
                </div>
            </div>
        @else
            <div class="row mt-sm-4">
                <div class="col-12 col-md-12 col-lg-5">
                    <div class="card profile-widget">
                        <div class="profile-widget-header">
                            <img alt="image" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="rounded-circle profile-widget-picture">
                            <div class="profile-widget-items">
                                <div class="profile-widget-item">
                                    <div class="profile-widget-item-label">NIK</div>
                                    <div class="profile-widget-item-value">
                                        @if ($pegawai->nik)
                                            {{ $pegawai->nik }}
                                        @else
                                            <i class="text-secondary text-small">--No Data--</i>
                                        @endif
                                    </div>
                                </div>
                                <div class="profile-widget-item">
                                    <div class="profile-widget-item-label">NIP</div>
                                    <div class="profile-widget-item-value mx-2">
                                        @if ($pegawai->nip)
                                            {{ $pegawai->nip }}
                                        @else
                                            <i class="text-secondary text-small">--No Data--</i>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="profile-widget-description">
                            <div class="profile-widget-name">
                                {{ $pegawai->nama }}
                                <div class="text-muted d-inline font-weight-normal">
                                    <div class="slash"></div>
                                    @if($pegawai->jabatan_id && $pegawai->jabatan)
                                        @if((int) $pegawai->jabatan_id === 28)
                                            {{ $pegawai->jabatan->jabatan . ' - ' . \App\Models\Pegawai::jabatanFungsionalLabel($pegawai->jabatan_fungsional) }}
                                        @else
                                            {{ $pegawai->jabatan->jabatan }}
                                        @endif
                                    @else
                                        <i class="text-secondary text-small">--No Data--</i>
                                    @endif
                                </div>
                            </div>
                            @php
                                $researcherIds = [
                                    [
                                        'label' => 'Google Scholar',
                                        'value' => $pegawai->id_gscholar,
                                        'url' => $pegawai->id_gscholar ? 'https://scholar.google.com/citations?user=' . $pegawai->id_gscholar : null,
                                        'icon' => 'fas fa-graduation-cap',
                                        'color' => 'primary',
                                    ],
                                    [
                                        'label' => 'SINTA',
                                        'value' => $pegawai->id_sinta,
                                        'url' => $pegawai->id_sinta ? 'https://sinta.kemdikbud.go.id/authors/profile/' . $pegawai->id_sinta : null,
                                        'icon' => 'fas fa-award',
                                        'color' => 'success',
                                    ],
                                    [
                                        'label' => 'Scopus',
                                        'value' => $pegawai->id_scopus,
                                        'url' => $pegawai->id_scopus ? 'https://www.scopus.com/authid/detail.uri?authorId=' . $pegawai->id_scopus : null,
                                        'icon' => 'fas fa-database',
                                        'color' => 'warning',
                                    ],
                                    [
                                        'label' => 'Garuda',
                                        'value' => $pegawai->id_garuda,
                                        'url' => $pegawai->id_garuda ? 'https://garuda.kemdikbud.go.id/author/view/' . $pegawai->id_garuda : null,
                                        'icon' => 'fas fa-feather-alt',
                                        'color' => 'danger',
                                    ],
                                    [
                                        'label' => 'WOS Researcher',
                                        'value' => $pegawai->id_wos,
                                        'url' => $pegawai->id_wos ? 'https://www.webofscience.com/wos/author/record/' . $pegawai->id_wos : null,
                                        'icon' => 'fas fa-globe',
                                        'color' => 'info',
                                    ],
                                    [
                                        'label' => 'ORCID',
                                        'value' => $pegawai->id_orc,
                                        'url' => $pegawai->id_orc ? 'https://orcid.org/' . $pegawai->id_orc : null,
                                        'icon' => 'fas fa-id-badge',
                                        'color' => 'dark',
                                    ],
                                ];
                            @endphp
                            @include('pegawai._researcher-ids', ['researcherIds' => $researcherIds])
                        </div>
                        {{-- <div class="card-footer text-center">
                            <div class="font-weight-bold mb-2">Sosial Media</div>
                            <a href="#" class="btn btn-social-icon btn-facebook mr-1"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="btn btn-social-icon btn-github mr-1"><i class="fab fa-github"></i></a>
                            <a href="#" class="btn btn-social-icon btn-google mr-1"><i class="fab fa-google"></i></a>
                            <a href="#" class="btn btn-social-icon btn-instagram mr-1"><i class="fab fa-instagram"></i></a>
                            <a href="#" class="btn btn-social-icon btn-twitter"><i class="fab fa-twitter"></i></a>
                        </div> --}}
                    </div>
                </div>

                <div class="col-12 col-sm-12 col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h4>Skor Kelengkapan Data</h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <span class="badge {{ $insight['score'] >= 80 ? 'badge-success' : ($insight['score'] >= 60 ? 'badge-warning' : 'badge-danger') }}">{{ $insight['score'] }}%</span>
                            </div>
                            <div class="progress mb-3" data-height="10">
                                <div class="progress-bar" role="progressbar" style="width: {{ $insight['score'] }}%" aria-valuenow="{{ $insight['score'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Skor Profil:</strong> {{ $insight['profile_score'] }}%</p>
                                    @if(!empty($insight['missing_profile_fields']))
                                        <small class="text-muted">Lengkapi: {{ implode(', ', $insight['missing_profile_fields']) }}.</small>
                                    @else
                                        <small class="text-success">Semua data profil utama sudah lengkap.</small>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Skor Dokumen:</strong> {{ $insight['document_score'] }}%</p>
                                    <small class="d-block text-muted mb-1">Dokumen valid {{ $insight['total_owned_documents'] }} dari {{ $insight['total_scored_documents'] }} dokumen master.</small>
                                    @if($insight['total_scored_documents'] === 0)
                                        <small class="text-muted">Master dokumen belum tersedia.</small>
                                    @elseif(!empty($insight['missing_documents']))
                                        <small class="text-muted">
                                            Dokumen belum ada:
                                            <ul>
                                                @foreach($insight['missing_documents'] as $item)
                                                    <li>{{ $item['nama'] }}</li>
                                                @endforeach
                                            </ul>
                                        </small>
                                    @else
                                        <small class="text-success">Dokumen valid pada master sudah lengkap.</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Jatah Cuti Card --}}
                    @if($cutiBreakdown)
                    <div class="card">
                        <div class="card-header">
                            <h4><i class="fas fa-umbrella-beach mr-1"></i> Jatah Cuti</h4>
                            <div class="card-header-action">
                                <span class="badge badge-{{ $cutiBreakdown['total_saldo'] >= 6 ? 'success' : ($cutiBreakdown['total_saldo'] >= 3 ? 'warning' : 'danger') }} px-3 py-2" style="font-size:1rem;">
                                    {{ $cutiBreakdown['total_saldo'] }} Hari
                                </span>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tahun</th>
                                        <th class="text-center">Jatah Awal</th>
                                        <th class="text-center">Terpakai</th>
                                        <th class="text-center">Sisa Jatah</th>
                                        <th class="text-center">Kontribusi Jatah</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(collect($cutiBreakdown['years'])->sortBy('tahun') as $yearData)
                                    <tr class="{{ $yearData['is_current_year'] ? 'table-primary' : '' }}">
                                        <td>
                                            {{ $yearData['tahun'] }}
                                            @if($yearData['is_current_year'])
                                                <span class="badge badge-primary ml-1">Tahun ini</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $yearData['hari_tersedia'] }} hari</td>
                                        <td class="text-center">{{ $yearData['hari_diambil'] }} hari</td>
                                        <td class="text-center">{{ $yearData['sisa'] }} hari</td>
                                        <td class="text-center">
                                            <strong>{{ $yearData['kontribusi_saldo'] }} hari</strong>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <td colspan="4" class="text-right font-weight-bold">Total Jatah Cuti Tersedia</td>
                                        <td class="text-center font-weight-bold">{{ $cutiBreakdown['total_saldo'] }} hari</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="card-footer">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Jatah dihitung dari tahun ini dan 2 tahun sebelumnya. Jatah tahun-tahun sebelumnya dibatasi maksimal <strong>{{ \App\Services\CutiService::MAX_CARRY_OVER }} hari</strong> sebagai carry-over, lalu pemakaian cuti akan mengurangi jatah tahun paling lama terlebih dahulu.
                            </small>
                        </div>
                    </div>
                    @endif
                </div>
                <div class="col-12 col-sm-12">
                    <div class="card">
                        <div class="card-header">
                            <h4>Form Profil Pegawai</h4>
                        </div>
                        <form method="POST" action="{{ route('pegawai.profile.update') }}">
                            @csrf
                            @method('PUT')

                            <div class="card-body">
                                @if($errors->getBag('default')->any())
                                    <div class="alert alert-danger">
                                        <ul class="mb-0 pl-3">
                                            @foreach($errors->getBag('default')->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>NIP <span class="text-danger">*</span></label>
                                        <input type="text" name="nip" class="form-control" value="{{ old('nip', $pegawai->nip) }}" readonly>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>NIK</label>
                                        <input type="text" name="nik" class="form-control" value="{{ old('nik', $pegawai->nik) }}">
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

                                <div class="form-row">
                                    <div class="form-group col-md-12">
                                        <label>Nama <span class="text-danger">*</span></label>
                                        <input type="text" name="nama" class="form-control" value="{{ old('nama', $pegawai->nama) }}" required>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $pegawai->tempat_lahir) }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Tanggal Lahir</label>
                                        <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', optional($pegawai->tanggal_lahir)->format('Y-m-d')) }}">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="form-control">
                                            <option value="">-- Pilih --</option>
                                            <option value="1" {{ (string) old('jenis_kelamin', !is_null($pegawai->jenis_kelamin) ? (int) $pegawai->jenis_kelamin : '') === '1' ? 'selected' : '' }}>Laki-laki</option>
                                            <option value="0" {{ (string) old('jenis_kelamin', !is_null($pegawai->jenis_kelamin) ? (int) $pegawai->jenis_kelamin : '') === '0' ? 'selected' : '' }}>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $pegawai->email) }}">
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Nomor HP</label>
                                        <input type="text" name="no_hp" class="form-control" value="{{ old('no_hp', $pegawai->no_hp) }}">
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Nomor Telepon</label>
                                        <input type="text" name="no_telp" class="form-control" value="{{ old('no_telp', $pegawai->no_telp) }}">
                                    </div>
                                </div>

                                <div class="border rounded p-3 mb-3" data-domisili-form>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h5 class="mb-0">Alamat Domisili</h5>
                                        <small class="text-muted">Lengkapi wilayah domisili hingga desa/kelurahan.</small>
                                    </div>
                                    <div class="form-group">
                                        <label>Alamat Domisili</label>
                                        <textarea name="alamat" rows="3" class="form-control" placeholder="Contoh: Jalan Merdeka No. 10">{{ old('alamat', $pegawai->alamat) }}</textarea>
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
                                        <div class="form-group col-md-6 mb-0">
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
                                    <div class="alert alert-light border mb-0 d-flex align-items-center">
                                        <small class="text-muted mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Gunakan bagian <strong>Identitas Akademik</strong> di atas untuk mengelola profil Google Scholar, SINTA, Scopus, Garuda, WOS, dan ORCID.
                                        </small>
                                    </div>
                                </div>

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Unit Kerja</label>
                                        <select name="unit_kerja_id" class="form-control">
                                            <option value="">-- Pilih Unit Kerja --</option>
                                            @foreach($unitKerjas as $unitKerja)
                                                <option value="{{ $unitKerja->id }}" {{ (string) old('unit_kerja_id', $pegawai->unit_kerja_id) === (string) $unitKerja->id ? 'selected' : '' }}>{{ $unitKerja->unit_kerja }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label>Program Studi</label>
                                        <select name="program_studi_id" class="form-control">
                                            <option value="">-- Pilih Program Studi --</option>
                                            @foreach($programStudis as $programStudi)
                                                <option value="{{ $programStudi->id }}" {{ (string) old('program_studi_id', $pegawai->program_studi_id) === (string) $programStudi->id ? 'selected' : '' }}>{{ $programStudi->jenjang }} - {{ $programStudi->nama_prodi }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label>Jabatan Fungsional</label>
                                    <select name="jabatan_fungsional" class="form-control">
                                        <option value="">-- Pilih Jabatan Fungsional --</option>
                                        @php
                                            $selectedJabatanFungsional = \App\Models\Pegawai::normalizeJabatanFungsional(old('jabatan_fungsional', $pegawai->jabatan_fungsional));
                                        @endphp
                                        @foreach($jabatanFungsionalOptions as $value => $label)
                                            <option value="{{ $value }}" {{ $selectedJabatanFungsional === $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif

        <div class="row mt-sm-4" id="password-security">
            <div class="col-12 col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Ubah Password</h4>
                    </div>
                    <form method="POST" action="{{ route('pegawai.profile.password.update') }}">
                        @csrf
                        @method('PUT')

                        <div class="card-body">
                            <p class="text-muted">
                                Gunakan menu profil untuk memperbarui password akun Anda secara mandiri. Masukkan password saat ini untuk konfirmasi.
                            </p>

                            @if($errors->updatePassword->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0 pl-3">
                                        @foreach($errors->updatePassword->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <div class="form-group">
                                <label>Password Saat Ini <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" class="form-control" autocomplete="current-password" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" autocomplete="new-password" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Konfirmasi Password Baru <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" required>
                                </div>
                            </div>

                            <small class="text-muted d-block">
                                Gunakan minimal 8 karakter dan kombinasi yang kuat agar akun tetap aman.
                            </small>
                        </div>

                        <div class="card-footer text-right">
                            <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('plugins_js')
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    @endpush

    @push('page_js')
        <script>
            $(function () {
                @include('pegawai._domisili-script')
            });
        </script>
    @endpush
</x-app-layout>

