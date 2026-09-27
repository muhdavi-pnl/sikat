<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('kepegawaian.cuti.proses') }}">Proses Cuti</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Perbarui status usulan cuti pegawai dari modul cuti tersendiri.</p>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4>Usulan: {{ optional($usulan->pegawai)->nama }} - {{ optional($usulan->layanan)->layanan ?: 'Layanan Cuti Pegawai' }}</h4>
                @if($usulan->status === \App\Models\LayananPegawai::STATUS_SELESAI)
                    <div class="card-header-action">
                        <a href="{{ route('pegawai.layanan.cuti.print', $usulan->id) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-print mr-1"></i> Cetak Formulir Cuti
                        </a>
                    </div>
                @endif
            </div>
            <div class="card-body">
                @php
                    $cuti = $usulan->cutiDetail;
                    $syaratUploads = collect($usulan->syarat_uploads ?? [])->keyBy('syarat_id');
                    $statusOptions = \App\Models\LayananPegawai::statusOptions();
                @endphp

                @if($usulan->status === \App\Models\LayananPegawai::STATUS_DIBATALKAN)
                    <div class="alert alert-dark d-flex align-items-center mb-4">
                        <i class="fas fa-ban fa-2x mr-3 text-warning"></i>
                        <div>
                            <h6 class="mb-1 font-weight-bold text-white">Usulan Cuti Telah Dibatalkan</h6>
                            <p class="mb-0 text-white-50">
                                Usulan cuti ini telah dibatalkan oleh kepegawaian. Jatah cuti pegawai sebesar <strong>{{ (int) optional($cuti)->hari_diminta }} hari</strong> telah otomatis dikembalikan ke saldo cuti aktif pegawai.
                            </p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('kepegawaian.cuti.update', $usulan->id) }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="layanan_id" value="{{ old('layanan_id', $usulan->layanan_id) }}">

                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label>Jenis Layanan</label>
                                <input type="text" class="form-control" value="{{ optional($usulan->layanan)->layanan ?: 'Layanan Cuti Pegawai' }}" readonly>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control @error('status') is-invalid @enderror" required>
                                    @foreach($statusOptions as $value => $label)
                                        <option value="{{ $value }}" {{ old('status', $usulan->status) === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="catatan_pengusul">Catatan Pengusul</label>
                        <textarea id="catatan_pengusul" class="form-control" rows="3" readonly>{{ $usulan->catatan_pengusul }}</textarea>
                    </div>

                    <div class="form-group">
                        <label>Informasi Cuti</label>
                        <div class="table-responsive border rounded">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <th style="width: 240px;">Status Pegawai</th>
                                        <td>
                                            @if($usulan->pegawai)
                                                <span class="badge {{ $usulan->pegawai->isPns() ? 'badge-primary' : ($usulan->pegawai->isPppk() ? 'badge-info' : 'badge-secondary') }}">
                                                    {{ $usulan->pegawai->status_pegawai }}
                                                </span>
                                                <span class="ml-2 text-muted">Masa Kerja: {{ $usulan->pegawai->getMasaKerjaTahun() }} tahun ({{ $usulan->pegawai->getMasaKerjaBulan() }} bulan)</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Jenis Cuti</th>
                                        <td><strong>{{ $cuti ? \App\Services\CutiService::jenisCutiLabel($cuti->jenis_cuti) : '-' }}</strong></td>
                                    </tr>
                                    @if($cuti && $cuti->kategori_cuti)
                                        <tr>
                                            <th>Kategori Cuti Sakit</th>
                                            <td><span class="badge badge-info">{{ \App\Models\CutiLayananPegawai::kategoriSakitOptions()[$cuti->kategori_cuti] ?? $cuti->kategori_cuti }}</span></td>
                                        </tr>
                                    @endif
                                    @if($cuti && $cuti->alasan_cap)
                                        <tr>
                                            <th>Alasan Cuti Alasan Penting</th>
                                            <td><strong>{{ \App\Models\CutiLayananPegawai::alasanCapOptions()[$cuti->alasan_cap] ?? $cuti->alasan_cap }}</strong></td>
                                        </tr>
                                    @endif
                                    @if($cuti && $cuti->alasan_pppk_bypass)
                                        <tr>
                                            <th>Alasan Bypass PPPK (&lt; 1 Thn)</th>
                                            <td><span class="badge badge-warning">{{ \App\Models\CutiLayananPegawai::alasanPppkBypassOptions()[$cuti->alasan_pppk_bypass] ?? $cuti->alasan_pppk_bypass }}</span></td>
                                        </tr>
                                    @endif
                                    @if($cuti && $cuti->kelahiran_anak_ke)
                                        <tr>
                                            <th>Kelahiran Anak Ke</th>
                                            <td>Anak ke-{{ $cuti->kelahiran_anak_ke }}</td>
                                        </tr>
                                    @endif
                                    @if($cuti && $cuti->rekomendasi_tim_penguji_kesehatan)
                                        <tr>
                                            <th>Tim Penguji Kesehatan</th>
                                            <td><span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Rekomendasi Terlampir (&gt; 365 hari)</span></td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>Tanggal Cuti</th>
                                        <td>
                                            @if($cuti && $cuti->tanggal_mulai && $cuti->tanggal_selesai)
                                                {{ $cuti->tanggal_mulai->format('d-m-Y') }} s.d. {{ $cuti->tanggal_selesai->format('d-m-Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Hari Cuti Diajukan</th>
                                        <td>{{ $cuti && $cuti->hari_diminta !== null ? ((int) $cuti->hari_diminta . ' hari') : '-' }}</td>
                                    </tr>
                                    @if(!$cuti || in_array($cuti->jenis_cuti, [null, '', 'tahunan', \App\Services\CutiService::JENIS_TAHUNAN], true))
                                        <tr>
                                            <th>Sisa Cuti Saat Pengajuan</th>
                                            <td>{{ $cuti && $cuti->hari_tersedia_saat_usul !== null ? ((int) $cuti->hari_tersedia_saat_usul . ' hari') : '-' }}</td>
                                        </tr>
                                        <tr>
                                            <th>Sisa Cuti Saat Ini</th>
                                            <td>{{ $usulan->pegawai ? app(\App\Services\CutiService::class)->getSaldoCuti($usulan->pegawai) : 0 }} hari</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>Alasan Cuti</th>
                                        <td>{{ $cuti && $cuti->alasan_cuti ? $cuti->alasan_cuti : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Alamat Selama Cuti</th>
                                        <td>{{ $cuti && $cuti->alamat_menjalankan_cuti ? $cuti->alamat_menjalankan_cuti : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>No. Telepon Selama Cuti</th>
                                        <td>{{ $cuti && $cuti->nomor_telepon_cuti ? $cuti->nomor_telepon_cuti : '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted d-block mt-2">
                            Catatan Sistem ASN:
                            @if($cuti && $cuti->jenis_cuti === \App\Services\CutiService::JENIS_BESAR)
                                Menyelesaikan Cuti Besar akan otomatis me-reset kuota Cuti Tahunan tahun berjalan (N) pegawai menjadi 0.
                            @elseif($cuti && $cuti->jenis_cuti === \App\Services\CutiService::JENIS_CLTN)
                                Menyelesaikan CLTN akan otomatis menonaktifkan status payroll (freeze gaji/tunjangan) dan status kedudukan pegawai menjadi CLTN.
                            @elseif($cuti && $cuti->jenis_cuti === \App\Services\CutiService::JENIS_TAHUNAN)
                                Menyelesaikan Cuti Tahunan akan memotong saldo kuota cuti tahunan pegawai sesuai FIFO bucket.
                            @else
                                Perubahan status usulan akan disinkronisasikan ke data kepegawaian ASN.
                            @endif
                        </small>
                    </div>

                    <div class="form-group">
                        <label>Daftar Syarat Layanan</label>
                        @if(optional($usulan->layanan)->syarat && $usulan->layanan->syarat->isNotEmpty())
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-striped mb-0">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Syarat</th>
                                            <th>Mapping</th>
                                            <th>Bukti Upload</th>
                                            <th>Status Review</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($usulan->layanan->syarat as $syarat)
                                            @php
                                                $upload = $syaratUploads->get($syarat->id);
                                                $reviewStatus = old('syarat_reviews.' . $syarat->id . '.status', $upload['review_status'] ?? 'pending');
                                                $reviewCatatan = old('syarat_reviews.' . $syarat->id . '.catatan', $upload['review_catatan'] ?? '');
                                            @endphp
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $syarat->syarat }}</td>
                                                <td>
                                                    <span class="badge {{ $syarat->mappingBadgeClass() }}">{{ $syarat->mappingLabel() }}</span>
                                                    @if($syarat->kode_syarat)
                                                        <div class="text-muted small mt-1">{{ $syarat->kode_syarat }}</div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($upload && !empty($upload['path']))
                                                        <a href="{{ asset('file/' . $upload['path']) }}" target="_blank" class="btn btn-sm btn-info">
                                                            <i class="fas fa-download"></i> Lihat Bukti
                                                        </a>
                                                        @if(!empty($upload['uploaded_at']))
                                                            <div class="text-muted small mt-1">Diunggah: {{ \Illuminate\Support\Carbon::parse($upload['uploaded_at'])->format('d-m-Y H:i') }}</div>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($upload && !empty($upload['path']))
                                                        <div class="mb-2">
                                                            @if($reviewStatus === 'approved')
                                                                <span class="badge badge-success">Bukti Disetujui</span>
                                                            @elseif($reviewStatus === 'rejected')
                                                                <span class="badge badge-danger">Bukti Ditolak</span>
                                                            @else
                                                                <span class="badge badge-warning">Menunggu Verifikasi</span>
                                                            @endif
                                                        </div>
                                                        <select name="syarat_reviews[{{ $syarat->id }}][status]" class="form-control form-control-sm mb-2 @error('syarat_reviews.' . $syarat->id . '.status') is-invalid @enderror">
                                                            <option value="pending" {{ $reviewStatus === 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                                                            <option value="approved" {{ $reviewStatus === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                                            <option value="rejected" {{ $reviewStatus === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                                        </select>
                                                        <textarea name="syarat_reviews[{{ $syarat->id }}][catatan]" rows="2" class="form-control form-control-sm @error('syarat_reviews.' . $syarat->id . '.catatan') is-invalid @enderror" placeholder="Catatan verifikasi bukti (opsional)">{{ $reviewCatatan }}</textarea>
                                                        @error('syarat_reviews.' . $syarat->id . '.status')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                        @error('syarat_reviews.' . $syarat->id . '.catatan')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    @else
                                                        <span class="text-muted">Tidak perlu verifikasi bukti tambahan</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-muted">Layanan ini tidak memiliki syarat.</div>
                        @endif
                    </div>

                    <div class="form-group">
                        <label for="catatan_proses">Catatan Proses</label>
                        <textarea name="catatan_proses" id="catatan_proses" class="form-control @error('catatan_proses') is-invalid @enderror" rows="4" placeholder="Tambahkan catatan proses (opsional)">{{ old('catatan_proses', $usulan->catatan_proses) }}</textarea>
                        @error('catatan_proses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="output_file">Output / Hasil Layanan</label>
                        <div class="custom-file">
                            <input type="file" name="output_file" id="output_file" class="custom-file-input @error('output_file') is-invalid @enderror" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                            <label class="custom-file-label" for="output_file">Pilih file output layanan</label>
                            @error('output_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <small class="form-text text-muted">Opsional. Unggah jika ada surat keputusan, lembar disposisi, atau dokumen hasil akhir layanan cuti yang perlu dibagikan ke pegawai.</small>

                        @if($usulan->output_path)
                            <div class="mt-3 p-3 border rounded bg-light">
                                <div class="d-flex flex-wrap align-items-center justify-content-between" style="gap: .75rem;">
                                    <div>
                                        <div class="font-weight-600">Output saat ini</div>
                                        <div>{{ $usulan->output_original_name ?: basename((string) $usulan->output_path) }}</div>
                                        <div class="text-muted small">
                                            @if($usulan->output_uploaded_at)
                                                Diunggah {{ $usulan->output_uploaded_at->format('d-m-Y H:i') }}
                                            @endif
                                            @if($usulan->outputUploader)
                                                oleh {{ $usulan->outputUploader->name }}
                                            @endif
                                        </div>
                                    </div>
                                    <a href="{{ asset('file/' . ltrim($usulan->output_path, '/')) }}" target="_blank" class="btn btn-sm btn-info">
                                        <i class="fas fa-download"></i> Lihat Output
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            @if($usulan->status === \App\Models\LayananPegawai::STATUS_SELESAI)
                                <button type="button" class="btn btn-outline-danger" data-toggle="modal" data-target="#modalBatalkanCuti">
                                    <i class="fas fa-undo-alt mr-1"></i> Batalkan Cuti
                                </button>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('kepegawaian.cuti.proses') }}" class="btn btn-outline-secondary mr-1">Kembali</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($usulan->status === \App\Models\LayananPegawai::STATUS_SELESAI)
        <div class="modal fade" id="modalBatalkanCuti" role="dialog" aria-labelledby="modalBatalkanCutiLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="POST" action="{{ route('kepegawaian.cuti.batalkan', $usulan->id) }}">
                        @csrf
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="modalBatalkanCutiLabel">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Konfirmasi Pembatalan Cuti
                            </h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning mb-3">
                                <i class="fas fa-info-circle mr-1"></i>
                                Membatalkan cuti ini akan mengubah status usulan menjadi <strong>Dibatalkan</strong> dan secara otomatis <strong>mengembalikan {{ (int) optional($cuti)->hari_diminta }} hari</strong> jatah cuti ke saldo pegawai ({{ optional($usulan->pegawai)->nama }}).
                            </div>

                            <div class="form-group">
                                <label for="alasan_pembatalan" class="font-weight-bold text-dark">
                                    Alasan Pembatalan <span class="text-danger">*</span>
                                </label>
                                <textarea
                                    name="alasan_pembatalan"
                                    id="alasan_pembatalan"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Tuliskan alasan lengkap pembatalan cuti yang telah selesai ini..."
                                    required
                                ></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-whitesmoke">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-undo-alt mr-1"></i> Ya, Batalkan Cuti & Kembalikan Saldo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @push('page_js')
        <script>
            $(function () {
                // Pindahkan modal ke <body> agar tidak tertutup backdrop / terhalang stacking context dari .main-content
                $('#modalBatalkanCuti').appendTo('body');

                $('#output_file').on('change', function () {
                    const fileName = this.files && this.files.length ? this.files[0].name : 'Pilih file output layanan';
                    $(this).next('.custom-file-label').text(fileName);
                });
            });
        </script>
    @endpush
</x-app-layout>
