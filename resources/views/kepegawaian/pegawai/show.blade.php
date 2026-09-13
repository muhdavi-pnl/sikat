<x-app-layout>
    @push('plugins_css')
        <link rel="stylesheet" href="{{ asset('assets/modules/bootstrap/bootstrap-social.css') }}">
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('kepegawaian.pegawai') }}">Pegawai</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Ringkasan data pribadi, kepegawaian, homebase, dan kontak pegawai.</p>

        <div class="row mt-sm-4">
            <div class="col-12 col-md-12 col-lg-5">
                <div class="card profile-widget">
                    <div class="profile-widget-header">
                        <img alt="image" src="{{ asset('assets/img/avatar/avatar-1.png') }}"
                             class="rounded-circle profile-widget-picture">
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
                            {{ $pegawai->gelar_depan ? $pegawai->gelar_depan . ' ' . strtoupper($pegawai->nama) . ', ' . $pegawai->gelar_belakang : strtoupper($pegawai->nama) . ', ' . $pegawai->gelar_belakang }}
                            <div class="text-muted d-inline font-weight-normal">
                                <div class="slash"></div>
                                @if($pegawai->jabatan_id)
                                    @if($pegawai->jabatan_id == 28)
                                        {{ $pegawai->jabatan->jabatan. " - " .\App\Models\Pegawai::jabatanFungsionalLabel($pegawai->jabatan_fungsional) }}
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
                        <h4>Data Pegawai</h4>
                        <div class="card-header-action">
                            <a href="{{ route('kepegawaian.dokumen.show', $pegawai) }}" class="btn btn-primary">
                                Kelola Dokumen
                            </a>
                            <a href="{{ route('kepegawaian.pegawai') }}" class="btn btn-outline-dark">
                                Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <ul class="nav nav-pills p-2" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active show" id="pribadi-tab" data-toggle="tab" href="#pribadi" role="tab" aria-controls="pribadi" aria-selected="true">Pribadi</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="kepegawaian-tab" data-toggle="tab" href="#kepegawaian" role="tab" aria-controls="kepegawaian" aria-selected="false">Kepegawaian</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="homebase-tab" data-toggle="tab" href="#homebase" role="tab" aria-controls="homebase" aria-selected="false">Homebase</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="kontak-tab" data-toggle="tab" href="#kontak" role="tab" aria-controls="kontak" aria-selected="false">Kontak</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="cuti-tab" data-toggle="tab" href="#cuti" role="tab" aria-controls="cuti" aria-selected="false">Jatah Cuti</a>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane fade active show" id="pribadi" role="tabpanel" aria-labelledby="pribadi-tab">
                                <table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">NIK</th>
                                        <td>
                                            @if ($pegawai->nik)
                                                {{ $pegawai->nik }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">NUPTK</th>
                                        <td>
                                            @if ($pegawai->nuptk)
                                                {{ $pegawai->nuptk }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">NIDN</th>
                                        <td>
                                            @if ($pegawai->nidn)
                                                {{ $pegawai->nidn }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Nama Lengkap</th>
                                        <th scope="row">
                                            @if($pegawai->gelar_depan and $pegawai->gelar_belakang)
                                                {{ $pegawai->gelar_depan }} {{ strtoupper($pegawai->nama) }}, {{ $pegawai->gelar_belakang }}
                                            @elseif($pegawai->gelar_belakang)
                                                {{ strtoupper($pegawai->nama) }}, {{ $pegawai->gelar_belakang }}
                                            @elseif($pegawai->gelar_depan)
                                                {{ $pegawai->gelar_depan }} {{ strtoupper($pegawai->nama) }}
                                            @else
                                                {{ strtoupper($pegawai->nama) }}
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Tempat, Tanggal Lahir</th>
                                        <td>
                                            @if ($pegawai->tempat_lahir and $pegawai->tanggal_lahir)
                                                {{ $pegawai->tempat_lahir }}, {{ $pegawai->tanggal_lahir }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Jenis Kelamin</th>
                                        <td>
                                            @if (!is_null($pegawai->jenis_kelamin))
                                                {{ $pegawai->jenis_kelamin ? 'Laki-laki' : 'Perempuan' }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Agama</th>
                                        <th scope="row">
                                            @if($pegawai->agama_id)
                                                {{ $pegawai->agama->agama }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Status Perkawinan</th>
                                        <th scope="row">
                                            @if($pegawai->status_perkawinan_id)
                                                {{ $pegawai->status_perkawinan->status_perkawinan }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Jumlah Anak</th>
                                        <th scope="row">
                                            @if($pegawai->jumlah_anak)
                                                {{ $pegawai->jumlah_anak }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Pendidikan Terakhir</th>
                                        <th scope="row">
                                            @if($pegawai->pendidikan_id)
                                                {{ $pegawai->pendidikan->pendidikan }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Tanggal Lulus</th>
                                        <th scope="row">
                                            @if($pegawai->tanggal_lulus)
                                                {{ Date('d-m-Y', strtotime($pegawai->tanggal_lulus)) }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">NPWP</th>
                                        <th scope="row">
                                            @if($pegawai->npwp)
                                                {{ $pegawai->npwp }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">BPJS</th>
                                        <th scope="row">
                                            @if($pegawai->bpjs)
                                                {{ $pegawai->bpjs }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="kepegawaian" role="tabpanel" aria-labelledby="kepegawaian-tab">
                                <table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Pangkat - Golongan</th>
                                        <th scope="row">
                                            @if($pegawai->pangkat_id)
                                                {{ $pegawai->pangkat->pangkat }} - {{ $pegawai->pangkat->golongan_ruang }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">TMT CPNS</th>
                                        <th scope="row">
                                            @if($pegawai->tmt_cpns)
                                                {{ Date('d-m-Y', strtotime($pegawai->tmt_cpns)) }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">TMT PNS</th>
                                        <th scope="row">
                                            @if($pegawai->tmt_pns)
                                                {{ Date('d-m-Y', strtotime($pegawai->tmt_pns)) }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Masa Kerja</th>
                                        <th scope="row">
                                            @if($pegawai->tmt_cpns)
                                                {{ \Carbon\Carbon::parse($pegawai->tmt_cpns)->diff(\Carbon\Carbon::now())->format('%y Tahun %m Bulan') }} (TMT CPNS)
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Jabatan Fungsional</th>
                                        <th scope="row">
                                            @if($pegawai->jabatan_fungsional)
                                                {{ \App\Models\Pegawai::jabatanFungsionalLabel($pegawai->jabatan_fungsional) }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Jabatan</th>
                                        <th scope="row">
                                            @if($pegawai->jabatan_id)
                                                {{ $pegawai->jabatan->jabatan }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">TMT Jabatan</th>
                                        <th scope="row">
                                            @if($pegawai->tmt_jabatan)
                                                {{ Date('d-m-Y', strtotime($pegawai->tmt_jabatan)) }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Jenis Jabatan</th>
                                        <th scope="row">
                                            @if($pegawai->jabatan_id && $pegawai->jabatan->jenis_jabatan)
                                                {{ ucwords($pegawai->jabatan->jenis_jabatan->jenis_jabatan) }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Eselon</th>
                                        <th scope="row">
                                            @if($pegawai->eselon_id)
                                                {{ $pegawai->eselon->eselon }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Unit Kerja</th>
                                        <th scope="row">
                                            @if($pegawai->unit_kerja_id)
                                                {{ $pegawai->unit_kerja->unit_kerja }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Status Pegawai</th>
                                        <th scope="row">
                                            @if($pegawai->status_pegawai)
                                                {{ $pegawai->status_pegawai }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Kedudukan Pegawai</th>
                                        <th scope="row">
                                            @if($pegawai->kedudukan_pegawai_id)
                                                {{ $pegawai->kedudukan_pegawai->kedudukan_pegawai }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Nomor KARPEG</th>
                                        <th scope="row">
                                            @if($pegawai->no_karpeg)
                                                {{ $pegawai->no_karpeg }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Nomor KARIS/KARSU</th>
                                        <th scope="row">
                                            @if($pegawai->no_karis_karsu)
                                                {{ $pegawai->no_karis_karsu }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="homebase" role="tabpanel" aria-labelledby="homebase-tab">
                                <table class="table table-striped">
                                    <tbody>

                                    <tr>
                                        <th scope="row" class="text-right text-muted">Perguruan Tinggi</th>
                                        <th scope="row">
                                            @if($pegawai->program_studi_id)
                                                {{ $pegawai->program_studi->jurusan->perguruan_tinggi->perguruan_tinggi }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Jurusan</th>
                                        <th scope="row">
                                            @if($pegawai->program_studi_id)
                                                {{ $pegawai->program_studi->jurusan->jurusan }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Program Studi</th>
                                        <th scope="row">
                                            @if($pegawai->program_studi_id)
                                                {{ $pegawai->program_studi->jenjang. '-' .$pegawai->program_studi->nama_prodi }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                        @endif
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="kontak" role="tabpanel" aria-labelledby="kontak-tab">
                                <table class="table table-striped">
                                    <tbody>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Alamat Domisili</th>
                                        <th scope="row">
                                            @if($pegawai->alamat)
                                                {{ $pegawai->alamat }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Kelurahan/Desa</th>
                                        <th scope="row">
                                            @if($pegawai->kelurahan_id)
                                                {{ $pegawai->kelurahan->desa }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Kecamatan</th>
                                        <th scope="row">
                                            @if($pegawai->kelurahan_id)
                                                {{ $pegawai->kelurahan->kecamatan->kecamatan }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                        @endif
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Kabupaten/Kota</th>
                                        <th scope="row">
                                            @if($pegawai->kelurahan_id)
                                                {{ $pegawai->kelurahan->kecamatan->kabupaten->kabupaten }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Provinsi</th>
                                        <th scope="row">
                                            @if($pegawai->kelurahan_id)
                                                {{ $pegawai->kelurahan->kecamatan->kabupaten->provinsi->provinsi }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Kode Pos</th>
                                        <th scope="row">
                                            @if($pegawai->kelurahan_id)
                                                {{ $pegawai->kelurahan->kode_pos }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Nomor HP</th>
                                        <th scope="row">
                                            @if($pegawai->no_hp)
                                                {{ $pegawai->no_hp }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Nomor Telepon</th>
                                        <th scope="row">
                                            @if($pegawai->no_telp)
                                                {{ $pegawai->no_telp }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="text-right text-muted">Email</th>
                                        <th scope="row">
                                            @if($pegawai->user_id)
                                                {{ $pegawai->user->email }}
                                            @else
                                                <i class="text-secondary text-small">--No Data--</i>
                                            @endif
                                        </th>
                                    </tr>
                                    </tbody>
                                </table>
                                {{--<div class="card-footer text-right">
                                    <button class="btn btn-primary">Update Data</button>
                                </div>--}}
                            </div>

                            {{-- Cuti Tab --}}
                            <div class="tab-pane fade" id="cuti" role="tabpanel" aria-labelledby="cuti-tab">
                                <div class="p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <h5 class="mb-0 mr-3">Total Jatah Cuti Tersedia</h5>
                                        <span class="badge badge-{{ $cutiBreakdown['total_saldo'] >= 6 ? 'success' : ($cutiBreakdown['total_saldo'] >= 3 ? 'warning' : 'danger') }} px-3 py-2" style="font-size:1rem;">
                                            {{ $cutiBreakdown['total_saldo'] }} Hari
                                        </span>
                                    </div>
                                    <table class="table table-sm table-bordered mb-2">
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
                                                <td colspan="4" class="text-right font-weight-bold">Total Jatah</td>
                                                <td class="text-center font-weight-bold">{{ $cutiBreakdown['total_saldo'] }} hari</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Jatah dihitung dari tahun ini dan 2 tahun sebelumnya. Jatah tahun-tahun sebelumnya dibatasi maksimal <strong>{{ \App\Services\CutiService::MAX_CARRY_OVER }} hari</strong> sebagai carry-over, lalu pemakaian cuti akan mengurangi jatah tahun paling lama terlebih dahulu.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--<div class="card">
                    <div class="card-header">
                        <h4>Daftar Dokumen</h4>
                        <div class="card-header-form">
                            <form>
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search">
                                    <div class="input-group-btn">
                                        <button class="btn btn-primary"><i class="fas fa-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <tr>
                                    <th>Task Name</th>
                                    <th>Progress</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                <tr>
                                    <td>Create a mobile app</td>
                                    <td class="align-middle">
                                        <div class="progress" data-height="4" data-toggle="tooltip" title="100%">
                                            <div class="progress-bar bg-success" data-width="100"></div>
                                        </div>
                                    </td>
                                    <td>2018-01-20</td>
                                    <td><div class="badge badge-success">Completed</div></td>
                                    <td><a href="#" class="btn btn-secondary">Detail</a></td>
                                </tr>
                                <tr><td>Redesign homepage</td>
                                    <td class="align-middle">
                                        <div class="progress" data-height="4" data-toggle="tooltip" title="0%">
                                            <div class="progress-bar" data-width="0"></div>
                                        </div>
                                    </td>
                                    <td>2018-04-10</td>
                                    <td><div class="badge badge-info">Todo</div></td>
                                    <td><a href="#" class="btn btn-secondary">Detail</a></td>
                                </tr>
                                <tr>
                                    <td>Backup database</td>
                                    <td class="align-middle">
                                        <div class="progress" data-height="4" data-toggle="tooltip" title="70%">
                                            <div class="progress-bar bg-warning" data-width="70"></div>
                                        </div>
                                    </td>
                                    <td>2018-01-29</td>
                                    <td><div class="badge badge-warning">In Progress</div></td>
                                    <td><a href="#" class="btn btn-secondary">Detail</a></td>
                                </tr>
                                <tr><td>Input data</td>
                                    <td class="align-middle">
                                        <div class="progress" data-height="4" data-toggle="tooltip" title="100%">
                                            <div class="progress-bar bg-success" data-width="100"></div>
                                        </div>
                                    </td>
                                    <td>2018-01-16</td>
                                    <td><div class="badge badge-success">Completed</div></td>
                                    <td><a href="#" class="btn btn-secondary">Detail</a></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>--}}
            </div>
        </div>
    </div>

    @push('plugins_js')
    @endpush

    @push('page_js')
    @endpush
</x-app-layout>

