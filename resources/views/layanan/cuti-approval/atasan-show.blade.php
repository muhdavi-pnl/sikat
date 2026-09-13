<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('cuti.approval.atasan.index') }}">Persetujuan Cuti (Atasan)</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Verifikasi dan berikan pertimbangan atas usulan cuti pegawai bawahan langsung.</p>

        @php
            $cuti = $usulan->cutiDetail;
            $pegawai = $usulan->pegawai;
            $statusOptions = [
                'disetujui' => 'DISETUJUI (Diteruskan ke Pejabat Yang Berwenang Memberikan Cuti / PYBMC)',
                'perubahan' => 'PERUBAHAN',
                'ditangguhkan' => 'DITANGGUHKAN',
                'tidak_disetujui' => 'TIDAK DISETUJUI',
            ];
            $currentAtasanStatus = $cuti?->atasan_status;
        @endphp

        <div class="row">
            <div class="col-12 col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h4>Data Pegawai & Usulan</h4>
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
                                    <th>Masa Kerja</th>
                                    <td>{{ $formData['masa_kerja'] ?? '-' }}</td>
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
            </div>

            <div class="col-12 col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Pertimbangan Atasan Langsung</h4>
                    </div>
                    <div class="card-body">
                        @if($currentAtasanStatus && $cuti?->atasan_approved_at)
                            <div class="alert alert-info">
                                <i class="fas fa-check-circle mr-1"></i> Pertimbangan telah disimpan pada <strong>{{ $cuti->atasan_approved_at->format('d/m/Y H:i') }}</strong> oleh <strong>{{ optional($cuti->atasanPegawai)->nama ?: optional($cuti->atasanUser)->name }}</strong>. Anda dapat memperbarui pertimbangan jika diperlukan.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('cuti.approval.atasan.approve', $usulan->id) }}">
                            @csrf

                            <div class="form-group">
                                <label class="d-block font-weight-bold">Keputusan / Pertimbangan Atasan Langsung <span class="text-danger">*</span></label>
                                @foreach($statusOptions as $val => $label)
                                    <div class="custom-control custom-radio mb-2">
                                        <input type="radio" id="atasan_status_{{ $val }}" name="atasan_status" value="{{ $val }}" class="custom-control-input" {{ old('atasan_status', $currentAtasanStatus ?? 'disetujui') === $val ? 'checked' : '' }} required>
                                        <label class="custom-control-label" for="atasan_status_{{ $val }}">
                                            <strong>{{ strtoupper($val) }}</strong> - {{ $label }}
                                        </label>
                                    </div>
                                @endforeach
                                @error('atasan_status')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="catatan_atasan" class="font-weight-bold">Catatan Pertimbangan (Opsional)</label>
                                <textarea name="catatan_atasan" id="catatan_atasan" class="form-control" rows="4" placeholder="Tuliskan catatan atau instruksi tambahan jika ada...">{{ old('catatan_atasan', $cuti->catatan_atasan ?? '') }}</textarea>
                                @error('catatan_atasan')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="{{ route('cuti.approval.atasan.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane mr-1"></i> Simpan & Teruskan Pertimbangan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
