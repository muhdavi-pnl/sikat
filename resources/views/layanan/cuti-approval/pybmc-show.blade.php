<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('cuti.approval.pybmc.index') }}">Persetujuan Cuti (PYBMC)</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Keputusan akhir pemberian cuti oleh Pejabat Yang Berwenang Memberikan Cuti (PYBMC).</p>

        @php
            $cuti = $usulan->cutiDetail;
            $pegawai = $usulan->pegawai;
            $statusOptions = [
                'disetujui' => 'DISETUJUI (Cuti Diberikan & Saldo Cuti Terpotong Otomatis)',
                'perubahan' => 'PERUBAHAN',
                'ditangguhkan' => 'DITANGGUHKAN',
                'tidak_disetujui' => 'TIDAK DISETUJUI (Tolak Usulan Cuti)',
            ];
            $currentPybmcStatus = $cuti?->pybmc_status;
        @endphp

        <div class="row">
            <div class="col-12 col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Usulan Cuti Pegawai</h4>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped table-sm mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 140px;">Nama Pegawai</th>
                                    <td><strong>{{ $pegawai->nama ?? '-' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>NIP</th>
                                    <td>{{ $pegawai->nip ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Jabatan</th>
                                    <td>{{ optional($pegawai->jabatan)->jabatan ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Unit Kerja</th>
                                    <td>{{ optional($pegawai->unit_kerja)->unit_kerja ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Pangkat / Gol.</th>
                                    <td>{{ optional($pegawai->pangkat)->nama ?: '-' }} ({{ optional($pegawai->pangkat)->golongan ?: '-' }})</td>
                                </tr>
                                <tr>
                                    <th>Jenis Cuti</th>
                                    <td><span class="badge badge-info">{{ $formData['jenis_cuti_label'] ?? 'Cuti Tahunan' }}</span></td>
                                </tr>
                                <tr>
                                    <th>Tanggal Cuti</th>
                                    <td>
                                        @if($cuti && $cuti->tanggal_mulai && $cuti->tanggal_selesai)
                                            <strong>{{ $cuti->tanggal_mulai->translatedFormat('d F Y') }}</strong> s.d. <strong>{{ $cuti->tanggal_selesai->translatedFormat('d F Y') }}</strong>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Jumlah Hari</th>
                                    <td><strong>{{ (int) ($cuti->hari_diminta ?? 0) }} Hari Kerja</strong></td>
                                </tr>
                                <tr>
                                    <th>Alasan Cuti</th>
                                    <td>{{ $cuti->alasan_cuti ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat Saat Cuti</th>
                                    <td>{{ $cuti->alamat_menjalankan_cuti ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>No. Telepon</th>
                                    <td>{{ $cuti->nomor_telepon_cuti ?: '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-whitesmoke text-center">
                        <a href="{{ route('pegawai.layanan.cuti.print', $usulan->id) }}" target="_blank" class="btn btn-outline-primary btn-sm btn-icon-split">
                            <i class="fas fa-print mr-1"></i> Preview Formulir Cuti
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4>Hasil Pertimbangan Atasan Langsung</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Atasan Langsung:</strong><br>
                            {{ optional($cuti?->atasanPegawai)->nama ?: 'Belum ditetapkan' }}
                            @if(optional($cuti?->atasanPegawai)->nip)
                                <span class="text-muted">(NIP: {{ $cuti->atasanPegawai->nip }})</span>
                            @endif
                        </div>
                        <div class="mb-2">
                            <strong>Pertimbangan:</strong><br>
                            @if($cuti?->atasan_status === 'disetujui')
                                <span class="badge badge-success">DISETUJUI</span>
                            @elseif($cuti?->atasan_status === 'perubahan')
                                <span class="badge badge-info">PERUBAHAN</span>
                            @elseif($cuti?->atasan_status === 'ditangguhkan')
                                <span class="badge badge-warning">DITANGGUHKAN</span>
                            @elseif($cuti?->atasan_status === 'tidak_disetujui')
                                <span class="badge badge-danger">TIDAK DISETUJUI</span>
                            @else
                                <span class="badge badge-secondary">-</span>
                            @endif
                            @if($cuti?->atasan_approved_at)
                                <span class="text-muted small ml-1">pada {{ $cuti->atasan_approved_at->format('d/m/Y H:i') }}</span>
                            @endif
                        </div>
                        @if($cuti?->catatan_atasan)
                            <div class="alert alert-light border mb-0">
                                <strong>Catatan Atasan:</strong><br>
                                {{ $cuti->catatan_atasan }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Keputusan PYBMC</h4>
                    </div>
                    <div class="card-body">
                        @if($currentPybmcStatus && $cuti?->pybmc_approved_at)
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle mr-1"></i> Keputusan telah disimpan pada <strong>{{ $cuti->pybmc_approved_at->format('d/m/Y H:i') }}</strong> oleh <strong>{{ optional($cuti->pybmcPegawai)->nama ?: optional($cuti->pybmcUser)->name }}</strong>. Anda dapat memperbarui keputusan jika diperlukan.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('cuti.approval.pybmc.approve', $usulan->id) }}" enctype="multipart/form-data">
                            @csrf

                            <div class="form-group">
                                <label class="d-block font-weight-bold">Keputusan Pejabat Yang Berwenang Memberikan Cuti <span class="text-danger">*</span></label>
                                @foreach($statusOptions as $val => $label)
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="pybmc_status_{{ $val }}" name="pybmc_status" value="{{ $val }}" class="custom-control-input" {{ old('pybmc_status', $currentPybmcStatus ?? 'disetujui') === $val ? 'checked' : '' }} required>
                                        <label class="custom-control-label" for="pybmc_status_{{ $val }}">
                                            <strong>{{ strtoupper($val) }}</strong> - {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                                @error('pybmc_status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="catatan_pybmc" class="font-weight-bold">Catatan Keputusan (Opsional)</label>
                                <textarea name="catatan_pybmc" id="catatan_pybmc" class="form-control" rows="4" placeholder="Tuliskan catatan keputusan PYBMC jika ada...">{{ old('catatan_pybmc', $cuti->catatan_pybmc ?? '') }}</textarea>
                                @error('catatan_pybmc')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="output_file" class="font-weight-bold">Upload Surat Izin / Keputusan Cuti (Opsional)</label>
                                <input type="file" name="output_file" id="output_file" class="form-control-file @error('output_file') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="form-text text-muted">Format: PDF/JPG/PNG, maksimal 5MB.</small>
                                @if($usulan->output_path)
                                    <div class="mt-2">
                                        <a href="{{ asset('file/' . $usulan->output_path) }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-file-download mr-1"></i> Lihat Dokumen Output Yang Sudah Diupload
                                        </a>
                                    </div>
                                @endif
                                @error('output_file')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="{{ route('cuti.approval.pybmc.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-double mr-1"></i> Simpan Keputusan Final PYBMC
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
