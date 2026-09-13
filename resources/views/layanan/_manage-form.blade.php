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

    <div class="card">
        <div class="card-header">
            <h4>Form {{ $title }}</h4>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-8">
                    <label>Nama Layanan <span class="text-danger">*</span></label>
                    <input type="text" name="layanan" class="form-control @error('layanan') is-invalid @enderror" value="{{ old('layanan', $layanan->layanan) }}" required>
                    @error('layanan')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group col-md-4">
                    <label>Jenis Layanan <span class="text-danger">*</span></label>
                    <select name="jenis" class="form-control @error('jenis') is-invalid @enderror" required>
                        <option value="">-- Pilih Jenis --</option>
                        @foreach($jenisOptions as $value => $label)
                            <option value="{{ $value }}" {{ old('jenis', $layanan->jenis) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('jenis')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="deskripsi" rows="4" class="form-control @error('deskripsi') is-invalid @enderror">{{ old('deskripsi', $layanan->deskripsi) }}</textarea>
                @error('deskripsi')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            @php
                $selectedSyaratIds = collect(old('syarat_ids', $layanan->relationLoaded('syarat') ? $layanan->syarat->pluck('id')->all() : []))
                    ->map(function ($id) {
                        return (string) $id;
                    })
                    ->all();
            @endphp

            <div class="form-group mb-0">
                <label>Persyaratan Terkait</label>
                <small class="text-muted d-block mb-3">Label <strong>Opsional untuk layanan cuti</strong> menandakan syarat tetap bisa dipasang ke layanan, tetapi hanya bersifat tidak memblokir saat dipakai pada layanan cuti.</small>
                <div class="row">
                    @forelse($syarats as $syarat)
                        @php
                            $isCutiOptionalRequirement = in_array((string) $syarat->kode_syarat, $cutiOptionalRequirementCodes ?? [], true);
                        @endphp
                        <div class="col-12 col-lg-6">
                            <label class="d-flex align-items-start border rounded p-3 mb-3" style="gap: 0.75rem; cursor: pointer;">
                                <input type="checkbox" name="syarat_ids[]" value="{{ $syarat->id }}" {{ in_array((string) $syarat->id, $selectedSyaratIds, true) ? 'checked' : '' }} style="margin-top: 0.25rem;">
                                <span>
                                    <span class="d-block font-weight-bold">{{ $syarat->syarat }}</span>
                                    @if($isCutiOptionalRequirement)
                                        <span class="d-inline-block badge badge-secondary mt-1 mb-1">Opsional untuk layanan cuti</span>
                                    @endif
                                    <span class="d-block text-muted small">
                                        @if($syarat->dokumen_id)
                                            Dokumen Pegawai: {{ optional($syarat->dokumen)->nama_dokumen ?: '-' }}
                                        @elseif($syarat->kode_syarat)
                                            Data Profil Pegawai: {{ $syarat->kode_syarat }}
                                        @else
                                            Verifikasi Manual
                                        @endif
                                    </span>
                                </span>
                            </label>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-warning mb-0">
                                Belum ada data syarat. Tambahkan syarat terlebih dahulu dari menu <a href="{{ route('syarat.create') }}">Syarat</a>.
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('layanan.index') }}" class="btn btn-secondary mr-2">Batal</a>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </div>
</form>

