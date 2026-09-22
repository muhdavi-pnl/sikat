<x-app-layout>
    @push('plugins_css')
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('layanan.kepegawaian') }}">Layanan</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        @php
            $isCutiLayanan = isset($isCutiLayanan) ? (bool) $isCutiLayanan : str_contains(mb_strtolower((string) $layanan->layanan), 'cuti');
            $cutiExcludedDateRules = app(\App\Services\CutiService::class)->getExcludedDateRules();
            $cutiJenisOptions = \App\Services\CutiService::jenisCutiOptions();
            $cutiHariTersedia = isset($cutiHariTersedia)
                ? (int) $cutiHariTersedia
                : (int) data_get($draft, 'cuti_hari_tersedia',
                    $isCutiLayanan && isset($pegawai)
                        ? app(\App\Services\CutiService::class)->getSaldoCuti($pegawai)
                        : \App\Services\CutiService::HARI_PER_TAHUN
                );
            $cutiTanggalMulai = isset($cutiTanggalMulai)
                ? (string) $cutiTanggalMulai
                : (string) data_get($draft, 'cuti_tanggal_mulai', old('cuti_tanggal_mulai', ''));
            $cutiTanggalSelesai = isset($cutiTanggalSelesai)
                ? (string) $cutiTanggalSelesai
                : (string) data_get($draft, 'cuti_tanggal_selesai', old('cuti_tanggal_selesai', ''));
            $cutiHariDiminta = isset($cutiHariDiminta)
                ? (int) $cutiHariDiminta
                : (int) data_get($draft, 'cuti_hari_diminta', old('cuti_hari_diminta', 0));
            $cutiJenis = isset($cutiJenis)
                ? (string) $cutiJenis
                : (string) data_get($draft, 'cuti_jenis', old('cuti_jenis', \App\Services\CutiService::resolveJenisCutiFromLayanan($layanan ?? null)));
            $cutiAlasan = isset($cutiAlasan)
                ? (string) $cutiAlasan
                : (string) data_get($draft, 'cuti_alasan', old('cuti_alasan', ''));
            $cutiAlamat = isset($cutiAlamat)
                ? (string) $cutiAlamat
                : (string) data_get($draft, 'cuti_alamat', old('cuti_alamat', optional($pegawai ?? null)->alamat ?: ''));
            $cutiNoTelp = isset($cutiNoTelp)
                ? (string) $cutiNoTelp
                : (string) data_get($draft, 'cuti_no_telp', old('cuti_no_telp', optional($pegawai ?? null)->no_hp ?: optional($pegawai ?? null)->no_telp ?: ''));
            $requirementSourceLabels = [
                'upload' => 'Unggah Bukti',
                'profile' => 'Data Profil Pegawai',
                'document' => 'Dokumen Pegawai',
                'generated' => 'Formulir Sistem',
            ];
        @endphp

        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">
            @if(($step ?? 'checklist') === 'review')
                Periksa kembali seluruh data usulan layanan Anda sebelum dikirim.
            @else
                Lengkapi ceklist persyaratan lalu lanjutkan ke tahap selesai untuk review usulan.
            @endif
        </p>

        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Form {{ $title }}</h4>
                        <div class="card-header-action">
                            @if(($step ?? 'checklist') === 'review')
                                <a href="{{ route('layanan.usul', $layanan->id) }}" class="btn btn-icon icon-left btn-warning">
                                    <i class="fas fa-arrow-left"></i> Kembali ke Ceklist
                                </a>
                            @endif
                            <a href="{{ route('pegawai.layanan') }}" class="btn btn-icon icon-left btn-dark {{ ($step ?? 'checklist') === 'review' ? 'ml-2' : '' }}">
                                <i class="fas fa-times"></i> Batal
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mt-2">
                            <div class="col-12 col-lg-8 offset-lg-2">
                                <div class="wizard-steps">
                                    <div class="wizard-step {{ ($step ?? 'checklist') === 'review' ? 'wizard-step-success' : 'wizard-step-active' }}">
                                        <div class="wizard-step-icon">
                                            <i class="fas fa-clipboard-list"></i>
                                        </div>
                                        <div class="wizard-step-label">
                                            Ceklist Persyaratan
                                        </div>
                                    </div>
                                    <div class="wizard-step {{ ($step ?? 'checklist') === 'review' ? 'wizard-step-active' : '' }}">
                                        <div class="wizard-step-icon">
                                            <i class="fas fa-check"></i>
                                        </div>
                                        <div class="wizard-step-label">
                                            Selesai
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(($step ?? 'checklist') === 'review')
                            <div class="wizard-content mt-2">
                                <div class="wizard-pane">
                                    <div class="alert alert-success">
                                        <div class="d-flex justify-content-between flex-wrap" style="gap: 0.5rem;">
                                            <strong>{{ $precheck['summary'] }}</strong>
                                            <span>Semua syarat telah diperiksa. Silakan review dan kirim usulan.</span>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-lg-6">
                                            <div class="card card-primary">
                                                <div class="card-header">
                                                    <h4>Informasi Layanan</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Nama Layanan</small>
                                                        <strong>{{ $layanan->layanan }}</strong>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Jenis Layanan</small>
                                                        <span class="badge badge-primary">{{ ucfirst($layanan->jenis) }}</span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Deskripsi</small>
                                                        <div>{{ $layanan->deskripsi ?: '-' }}</div>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Catatan Pengusul</small>
                                                        <div>{{ data_get($draft, 'catatan_pengusul') ?: '-' }}</div>
                                                    </div>
                                                    @if($isCutiLayanan)
                                                        <hr>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Jenis Cuti</small>
                                                            <strong>{{ \App\Services\CutiService::jenisCutiLabel($cutiJenis) }}</strong>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Rentang Tanggal Cuti</small>
                                                            <strong>
                                                                {{ $cutiTanggalMulai ? \Carbon\Carbon::parse($cutiTanggalMulai)->format('d-m-Y') : '-' }}
                                                                <span class="mx-1">s.d.</span>
                                                                {{ $cutiTanggalSelesai ? \Carbon\Carbon::parse($cutiTanggalSelesai)->format('d-m-Y') : '-' }}
                                                            </strong>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Jumlah Hari Cuti Diajukan</small>
                                                            <strong>{{ max(0, $cutiHariDiminta) }} hari</strong>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Alasan Cuti</small>
                                                            <div>{{ $cutiAlasan ?: '-' }}</div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Alamat Selama Menjalankan Cuti</small>
                                                            <div>{{ $cutiAlamat ?: '-' }}</div>
                                                        </div>
                                                        <div class="mb-2">
                                                            <small class="text-muted d-block">Nomor Telepon Selama Cuti</small>
                                                            <div>{{ $cutiNoTelp ?: '-' }}</div>
                                                        </div>
                                                        <div>
                                                            <small class="text-muted d-block">Sisa Jatah Cuti Saat Pengajuan</small>
                                                            <strong>{{ max(0, $cutiHariTersedia) }} hari</strong>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 col-lg-6">
                                            <div class="card card-info">
                                                <div class="card-header">
                                                    <h4>Data Pegawai</h4>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Nama</small>
                                                        <strong>{{ strtoupper($pegawai->nama) }}</strong>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">NIP</small>
                                                        <div>{{ $pegawai->nip ?: '-' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Status Pegawai</small>
                                                        <div>{{ $pegawai->status_pegawai ?: '-' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Unit Kerja</small>
                                                        <div>{{ optional($pegawai->unit_kerja)->unit_kerja ?: '-' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Program Studi</small>
                                                        <div>{{ optional($pegawai->program_studi)->nama_prodi ?: '-' }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Jabatan</small>
                                                        <div>{{ $pegawai->jabatan_fungsional ? \App\Models\Pegawai::jabatanFungsionalLabel($pegawai->jabatan_fungsional) : (optional($pegawai->jabatan)->jabatan ?? '-') }}</div>
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted d-block">Email</small>
                                                        <div>{{ $pegawai->email ?: optional($pegawai->user)->email ?: '-' }}</div>
                                                    </div>
                                                    <div>
                                                        <small class="text-muted d-block">Nomor HP</small>
                                                        <div>{{ $pegawai->no_hp ?: '-' }}</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mt-2">
                                        <div class="card-header">
                                            <h4>Ringkasan Persyaratan</h4>
                                        </div>
                                        <div class="card-body">
                                            <div class="pricing-details">
                                                @forelse($precheck['requirements'] as $requirement)
                                                    @php
                                                        $draftUpload = $draftUploadsBySyaratId->get($requirement['id']);
                                                        $requirementStatus = $requirement['status'] ?? 'unmet';
                                                        $isOptionalRequirement = (bool) ($requirement['is_optional'] ?? false);
                                                        $requirementIconClass = $requirementStatus === 'met'
                                                            ? 'bg-success text-white'
                                                            : ($requirementStatus === 'unmet'
                                                                ? 'bg-warning text-white'
                                                                : 'bg-light text-dark border');
                                                        $requirementIcon = $requirementStatus === 'met'
                                                            ? 'fa-check'
                                                            : ($requirementStatus === 'unmet'
                                                                ? 'fa-exclamation-triangle'
                                                                : 'fa-info-circle');
                                                    @endphp
                                                    <div class="pricing-item align-items-start">
                                                        <div class="pricing-item-icon {{ $requirementIconClass }}">
                                                            <i class="fas {{ $requirementIcon }}"></i>
                                                        </div>
                                                        <div class="pricing-item-label w-100">
                                                            <div class="d-flex justify-content-between flex-wrap" style="gap: 0.5rem;">
                                                                <strong>{{ $requirement['syarat'] }}</strong>
                                                                <span>
                                                                    <span class="badge badge-light">{{ $requirementSourceLabels[$requirement['source']] ?? ucfirst((string) $requirement['source']) }}</span>
                                                                    @if($isOptionalRequirement)
                                                                        <span class="badge badge-secondary">Opsional</span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <small class="text-muted d-block mt-1">{{ $requirement['message'] }}</small>
                                                            @if($requirement['source'] === 'upload' && $draftUpload)
                                                                <div class="mt-2 p-2 bg-light rounded border">
                                                                    <div><strong>Bukti upload:</strong> {{ $draftUpload['original_name'] ?? '-' }}</div>
                                                                    <small class="text-muted">Ukuran {{ isset($draftUpload['size']) ? number_format($draftUpload['size'] / 1024, 0) : '0' }} KB · Siap dikirim</small>
                                                                </div>
                                                            @elseif($requirement['source'] === 'generated')
                                                                <div class="mt-2"><small class="text-muted">Formulir akan dihasilkan otomatis dari data cuti pada usulan ini.</small></div>
                                                            @elseif($requirement['source'] === 'profile')
                                                                <div class="mt-2"><small class="text-muted">Sumber data: data profil pegawai.</small></div>
                                                            @elseif($requirement['source'] === 'document')
                                                                <div class="mt-2"><small class="text-muted">Sumber data: dokumen pegawai yang valid.</small></div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="pricing-item">
                                                        <div class="pricing-item-icon bg-success text-white">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                        <div class="pricing-item-label">Belum ada syarat yang terdaftar untuk layanan ini.</div>
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>

                                    <div class="card mt-2 border border-primary">
                                        <div class="card-header">
                                            <h4>Konfirmasi Pengiriman</h4>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" action="{{ route('pegawai.layanan.store', $layanan->id) }}">
                                                @csrf
                                                <div class="form-group mb-3">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" name="konfirmasi_kirim" value="1" class="custom-control-input @error('konfirmasi_kirim') is-invalid @enderror" id="konfirmasi_kirim" {{ old('konfirmasi_kirim') ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="konfirmasi_kirim">
                                                            Saya sudah memeriksa data layanan, persyaratan, dan data pegawai. Saya siap mengirim usulan ini.
                                                        </label>
                                                    </div>
                                                    @error('konfirmasi_kirim')
                                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="d-flex flex-wrap" style="gap: 0.75rem;">
                                                    <a href="{{ route('layanan.usul', $layanan->id) }}" class="btn btn-light border">Kembali</a>
                                                    <button type="submit" class="btn btn-primary">
                                                        Kirim Usulan <i class="fas fa-paper-plane"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <form class="wizard-content mt-2" method="POST" action="{{ route('pegawai.layanan.preview', $layanan->id) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="wizard-pane">
                                    <div class="alert {{ $precheck['eligible'] ? ($precheck['manual_count'] > 0 ? 'alert-info' : 'alert-success') : 'alert-warning' }}">
                                        <div class="d-flex justify-content-between flex-wrap" style="gap: 0.5rem;">
                                            <strong>{{ $precheck['summary'] }}</strong>
                                            <span>
                                                Terpenuhi {{ $precheck['met_count'] }}/{{ $precheck['total_count'] }}
                                                @if($precheck['manual_count'] > 0)
                                                    · Verifikasi Manual {{ $precheck['manual_count'] }}
                                                @endif
                                                @if(($precheck['optional_missing_count'] ?? 0) > 0)
                                                    · Opsional belum dilampirkan {{ $precheck['optional_missing_count'] }}
                                                @endif
                                            </span>
                                        </div>
                                        @if(!$precheck['eligible'])
                                            <div class="mt-2">Lengkapi syarat yang belum terpenuhi pada profil atau dokumen Anda sebelum lanjut ke tahap selesai.</div>
                                        @elseif($precheck['manual_count'] > 0)
                                            <div class="mt-2">Sebagian syarat belum dapat dicek otomatis dan tetap akan diverifikasi oleh petugas kepegawaian.</div>
                                        @elseif(($precheck['optional_missing_count'] ?? 0) > 0)
                                            <div class="mt-2">Sebagian syarat bersifat opsional. Anda tetap bisa lanjut, dan dapat melengkapinya kemudian bila diperlukan.</div>
                                        @else
                                            <div class="mt-2">Jika semua sudah sesuai, lanjutkan ke tahap selesai untuk review sebelum kirim usulan.</div>
                                        @endif
                                    </div>

                                    <div class="pricing pricing-highlight">
                                        <div class="pricing-title">
                                            Layanan {{ ucfirst($layanan->jenis) }}
                                        </div>
                                        <div class="pricing-padding">
                                            <div class="pricing-price">
                                                <div>{{ $layanan->layanan }}</div>
                                            </div>
                                            <div class="pricing-details">
                                                @forelse($precheck['requirements'] as $requirement)
                                                    @php
                                                        $draftUpload = $draftUploadsBySyaratId->get($requirement['id']);
                                                        $requirementStatus = $requirement['status'] ?? 'unmet';
                                                        $isOptionalRequirement = (bool) ($requirement['is_optional'] ?? false);
                                                        $requirementIconClass = $requirementStatus === 'met'
                                                            ? 'bg-success text-white'
                                                            : ($requirementStatus === 'unmet'
                                                                ? 'bg-warning text-white'
                                                                : 'bg-light text-dark border');
                                                        $requirementIcon = $requirementStatus === 'met'
                                                            ? 'fa-check'
                                                            : ($requirementStatus === 'unmet'
                                                                ? 'fa-exclamation-triangle'
                                                                : 'fa-info-circle');
                                                    @endphp
                                                    <div class="pricing-item align-items-start">
                                                        <div class="pricing-item-icon {{ $requirementIconClass }}">
                                                            <i class="fas {{ $requirementIcon }}"></i>
                                                        </div>
                                                        <div class="pricing-item-label">
                                                            <div class="d-flex justify-content-between flex-wrap" style="gap: 0.5rem;">
                                                                <span>{{ $requirement['syarat'] }}</span>
                                                                <span>
                                                                    <span class="badge badge-light">{{ $requirementSourceLabels[$requirement['source']] ?? ucfirst((string) $requirement['source']) }}</span>
                                                                    @if($isOptionalRequirement)
                                                                        <span class="badge badge-secondary">Opsional</span>
                                                                    @endif
                                                                </span>
                                                            </div>
                                                            <small class="text-muted d-block">{{ $requirement['message'] }}</small>
                                                            @if($requirement['source'] === 'upload')
                                                                @if($draftUpload)
                                                                    <div class="alert alert-light border mt-2 mb-2 py-2 px-3">
                                                                        <div><strong>Bukti draft tersimpan:</strong> {{ $draftUpload['original_name'] ?? '-' }}</div>
                                                                        <small class="text-muted">Unggah file baru hanya jika ingin mengganti bukti yang sudah dipilih.</small>
                                                                    </div>
                                                                @endif
                                                                <div class="mt-2">
                                                                    <label class="mb-1">Unggah Bukti Syarat{{ $isOptionalRequirement ? ' (opsional)' : '' }}</label>
                                                                    <input type="file" name="syarat_files[{{ $requirement['id'] }}]" class="form-control {{ $errors->has('syarat_files.' . $requirement['id']) ? 'is-invalid' : '' }}" accept=".pdf,.jpg,.jpeg,.png">
                                                                    @if($errors->has('syarat_files.' . $requirement['id']))
                                                                        <div class="invalid-feedback">{{ $errors->first('syarat_files.' . $requirement['id']) }}</div>
                                                                    @endif
                                                                    <small class="text-muted">Format PDF/JPG/JPEG/PNG, maksimum 2MB{{ $isOptionalRequirement ? '. Boleh dikosongkan jika tidak diperlukan.' : '.' }}</small>
                                                                </div>
                                                            @elseif($requirement['source'] === 'generated')
                                                                <div class="alert alert-light border mt-2 mb-0 py-2 px-3">
                                                                Formulir cuti akan dihasilkan otomatis dari data usulan dan bisa dicetak setelah usulan dikirim.
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="pricing-item">
                                                        <div class="pricing-item-icon bg-success text-white">
                                                            <i class="fas fa-check"></i>
                                                        </div>
                                                        <div class="pricing-item-label">Belum ada syarat yang terdaftar untuk layanan ini.</div>
                                                    </div>
                                                @endforelse
                                            </div>

                                            @if($isCutiLayanan)
                                                <div class="card border-primary mt-3 mb-0">
                                                    <div class="card-body py-3">
                                                        <h6 class="mb-3">Data Cuti</h6>
                                                        <div class="row">
                                                            <div class="col-12">
                                                                <div class="form-group mb-2">
                                                                    <label>Sisa Cuti Tersedia</label>
                                                                    <input type="text" class="form-control" value="{{ max(0, $cutiHariTersedia) }} hari" readonly>
                                                                </div>
                                                            </div>
                                                            <div class="col-12">
                                                                <div class="form-group mb-2">
                                                                    <label for="cuti_alasan">Alasan Cuti <span class="text-danger">*</span></label>
                                                                    <textarea name="cuti_alasan" id="cuti_alasan" rows="3" class="form-control @error('cuti_alasan') is-invalid @enderror" required>{{ $cutiAlasan }}</textarea>
                                                                    @error('cuti_alasan')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-8">
                                                                <div class="form-group mb-2">
                                                                    <label for="cuti_alamat">Alamat Selama Menjalankan Cuti <span class="text-danger">*</span></label>
                                                                    <textarea name="cuti_alamat" id="cuti_alamat" rows="2" class="form-control @error('cuti_alamat') is-invalid @enderror" required>{{ $cutiAlamat }}</textarea>
                                                                    @error('cuti_alamat')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-4">
                                                                <div class="form-group mb-2">
                                                                    <label for="cuti_no_telp">No. Telepon <span class="text-danger">*</span></label>
                                                                    <input type="text" name="cuti_no_telp" id="cuti_no_telp" class="form-control @error('cuti_no_telp') is-invalid @enderror" value="{{ $cutiNoTelp }}" required>
                                                                    @error('cuti_no_telp')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <div class="form-group mb-2">
                                                                    <label for="cuti_tanggal_mulai">Tanggal Mulai Cuti <span class="text-danger">*</span></label>
                                                                    <input type="date" name="cuti_tanggal_mulai" id="cuti_tanggal_mulai" class="form-control @error('cuti_tanggal_mulai') is-invalid @enderror" value="{{ $cutiTanggalMulai }}" required>
                                                                    @error('cuti_tanggal_mulai')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <div class="form-group mb-2">
                                                                    <label for="cuti_tanggal_selesai">Tanggal Selesai Cuti <span class="text-danger">*</span></label>
                                                                    <input type="date" name="cuti_tanggal_selesai" id="cuti_tanggal_selesai" class="form-control @error('cuti_tanggal_selesai') is-invalid @enderror" value="{{ $cutiTanggalSelesai }}" required>
                                                                    @error('cuti_tanggal_selesai')
                                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <div class="form-group mb-2">
                                                                    <label for="cuti_hari_diminta_preview">Jumlah Hari Cuti (Hari Kerja)</label>
                                                                    <input type="text" id="cuti_hari_diminta_preview" class="form-control @error('cuti_hari_diminta') is-invalid @enderror" value="{{ $cutiHariDiminta > 0 ? $cutiHariDiminta . ' hari kerja' : '' }}" readonly>
                                                                    @error('cuti_hari_diminta')
                                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <small class="text-muted d-block">Lengkapi data ini untuk membentuk formulir Lampiran 1.B yang bisa dicetak setelah usulan dikirim. Jumlah hari cuti dihitung otomatis dari rentang tanggal yang dipilih. Hanya hari kerja (Senin-Jumat) yang dihitung, tanggal libur yang dikonfigurasi tidak ikut dihitung, dan totalnya harus kurang dari atau sama dengan sisa cuti tersedia.</small>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="form-group mt-4">
                                                <label for="catatan_pengusul">Catatan Usulan (opsional)</label>
                                                <textarea name="catatan_pengusul" id="catatan_pengusul" rows="2" class="form-control @error('catatan_pengusul') is-invalid @enderror" placeholder="Contoh: Mohon diproses untuk kebutuhan administrasi semester ini">{{ old('catatan_pengusul', data_get($draft, 'catatan_pengusul')) }}</textarea>
                                                @error('catatan_pengusul')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="pricing-cta pb-3">
                                            <button type="submit" class="btn btn-primary">
                                                Buat Usulan <i class="fas fa-arrow-right"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @push('plugins_js')
        @endpush

        @push('page_js')
                @if($isCutiLayanan)
                    <script>
                        (function () {
                            const mulaiInput = document.getElementById('cuti_tanggal_mulai');
                            const selesaiInput = document.getElementById('cuti_tanggal_selesai');
                            const hasilInput = document.getElementById('cuti_hari_diminta_preview');
                            const exactExcludedDates = new Set(@json(array_values((array) ($cutiExcludedDateRules['exact_dates'] ?? []))));
                            const recurringExcludedDates = new Set(@json(array_values((array) ($cutiExcludedDateRules['recurring_dates'] ?? []))));

                            if (!mulaiInput || !selesaiInput || !hasilInput) {
                                return;
                            }

                            const calculateWorkingDays = () => {
                                if (!mulaiInput.value || !selesaiInput.value) {
                                    hasilInput.value = '';
                                    return;
                                }

                                const mulai = new Date(mulaiInput.value + 'T00:00:00');
                                const selesai = new Date(selesaiInput.value + 'T00:00:00');

                                if (Number.isNaN(mulai.getTime()) || Number.isNaN(selesai.getTime()) || mulai > selesai) {
                                    hasilInput.value = '';
                                    return;
                                }

                                let count = 0;
                                const cursor = new Date(mulai);
                                while (cursor <= selesai) {
                                    const day = cursor.getDay();
                                    const year = cursor.getFullYear();
                                    const month = String(cursor.getMonth() + 1).padStart(2, '0');
                                    const date = String(cursor.getDate()).padStart(2, '0');
                                    const isoDate = `${year}-${month}-${date}`;
                                    const recurringDate = `${month}-${date}`;
                                    if (day !== 0 && day !== 6 && !exactExcludedDates.has(isoDate) && !recurringExcludedDates.has(recurringDate)) {
                                        count++;
                                    }
                                    cursor.setDate(cursor.getDate() + 1);
                                }

                                hasilInput.value = count > 0 ? `${count} hari kerja` : '0 hari kerja';
                            };

                            mulaiInput.addEventListener('change', calculateWorkingDays);
                            selesaiInput.addEventListener('change', calculateWorkingDays);
                            calculateWorkingDays();
                        })();
                    </script>
                @endif
        @endpush
</x-app-layout>
