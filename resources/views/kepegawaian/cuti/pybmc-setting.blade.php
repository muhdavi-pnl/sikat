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
        <p class="section-lead">
            Penetapan Pejabat Yang Berwenang Memberikan Cuti (PYBMC) secara manual oleh Bagian Kepegawaian untuk proses persetujuan akhir usulan cuti pegawai.
        </p>

        <div class="row">
            <div class="col-12 col-md-5">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>PYBMC Saat Ini (Aktif)</h4>
                    </div>
                    <div class="card-body">
                        @if($currentSetting && $currentSetting->pegawai)
                            @php
                                $pejabat = $currentSetting->pegawai;
                            @endphp
                            <div class="text-center mb-3">
                                <div class="avatar-item">
                                    <img alt="image" src="{{ asset('assets/img/avatar/avatar-1.png') }}" class="img-fluid rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                                </div>
                                <h5 class="mt-2 mb-0">{{ $pejabat->nama }}</h5>
                                <span class="badge badge-success mt-1">Status: Aktif</span>
                            </div>
                            <table class="table table-sm table-striped">
                                <tr>
                                    <th>NIP</th>
                                    <td>{{ $pejabat->nip ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Jabatan PYBMC</th>
                                    <td>
                                        <strong>{{ $currentSetting->jabatan_label ?: (optional($pejabat->jabatan)->jabatan ?: 'Pejabat Yang Berwenang Memberikan Cuti') }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Unit Kerja</th>
                                    <td>{{ optional($pejabat->unit_kerja)->unit_kerja ?: '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Ditetapkan Pada</th>
                                    <td>{{ optional($currentSetting->created_at)->format('d F Y, H:i') }}</td>
                                </tr>
                            </table>
                        @else
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle mr-1"></i> Belum ada Pejabat Yang Berwenang Memberikan Cuti (PYBMC) yang ditetapkan secara aktif. Silakan pilih pejabat di form sebelah kanan.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-7">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Penetapan Pejabat PYBMC</h4>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('kepegawaian.cuti.pybmc-setting.update') }}">
                            @csrf

                            <div class="form-group">
                                <label for="pegawai_id">Pilih Pegawai Sebagai PYBMC <span class="text-danger">*</span></label>
                                <select name="pegawai_id" id="pegawai_id" class="form-control select2 @error('pegawai_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Pegawai --</option>
                                    @foreach($pegawais as $peg)
                                        <option value="{{ $peg->id }}" {{ (old('pegawai_id', optional($currentSetting)->pegawai_id) == $peg->id) ? 'selected' : '' }}>
                                            {{ $peg->nama }} (NIP: {{ $peg->nip ?: '-' }}) - {{ optional($peg->jabatan)->jabatan ?: '-' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('pegawai_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="jabatan_label">Label / Nama Jabatan PYBMC (Opsional)</label>
                                <input type="text" name="jabatan_label" id="jabatan_label" class="form-control @error('jabatan_label') is-invalid @enderror" placeholder="Contoh: Rektor / Direktur / Wakil Rektor II" value="{{ old('jabatan_label', optional($currentSetting)->jabatan_label) }}">
                                <small class="form-text text-muted">
                                    Jika dikosongkan, sistem akan menggunakan nama jabatan resmi pegawai tersebut.
                                </small>
                                @error('jabatan_label')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i> Simpan & Tetapkan PYBMC
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
