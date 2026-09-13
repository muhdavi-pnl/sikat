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

@php
    $selectedMappingSource = old('mapping_source', $mappingSource ?? 'manual');
    $isCutiOptionalRequirement = in_array((string) $syarat->kode_syarat, $cutiOptionalRequirementCodes ?? [], true);
@endphp

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
            @if($isCutiOptionalRequirement)
                <div class="alert alert-light border">
                    <i class="fas fa-info-circle mr-1"></i>
                    Kode syarat ini ditandai <strong>opsional untuk layanan cuti</strong>. Penanda ini hanya memengaruhi tampilan dan evaluasi khusus cuti, bukan struktur form master syarat.
                </div>
            @endif

            <div class="form-group">
                <label>Nama Syarat <span class="text-danger">*</span></label>
                <input type="text" name="syarat" class="form-control @error('syarat') is-invalid @enderror" value="{{ old('syarat', $syarat->syarat) }}" required>
                @error('syarat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Sumber Mapping <span class="text-danger">*</span></label>
                <select name="mapping_source" id="mapping_source" class="form-control @error('mapping_source') is-invalid @enderror" required>
                    <option value="manual" {{ $selectedMappingSource === 'manual' ? 'selected' : '' }}>Verifikasi Manual</option>
                    <option value="document" {{ $selectedMappingSource === 'document' ? 'selected' : '' }}>Dokumen Pegawai</option>
                    <option value="profile" {{ $selectedMappingSource === 'profile' ? 'selected' : '' }}>Data Profil Pegawai</option>
                </select>
                @error('mapping_source')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted d-block mt-2">
                    Verifikasi manual: dicek petugas. Dokumen pegawai: membutuhkan dokumen pegawai yang valid. Data profil pegawai: membutuhkan data profil pegawai tertentu.
                </small>
            </div>

            <div id="document-mapping-group" class="form-group {{ $selectedMappingSource === 'document' ? '' : 'd-none' }}">
                <label>Dokumen Pegawai <span class="text-danger">*</span></label>
                <select name="dokumen_id" class="form-control @error('dokumen_id') is-invalid @enderror">
                    <option value="">-- Pilih Dokumen --</option>
                    @foreach($dokumens as $dokumen)
                        <option value="{{ $dokumen->id }}" {{ (string) old('dokumen_id', $syarat->dokumen_id) === (string) $dokumen->id ? 'selected' : '' }}>
                            {{ $dokumen->nama_dokumen }} ({{ $dokumen->kode_dokumen }})
                        </option>
                    @endforeach
                </select>
                @error('dokumen_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div id="profile-mapping-group" class="form-group {{ $selectedMappingSource === 'profile' ? '' : 'd-none' }}">
                <label>Kode Profil <span class="text-danger">*</span></label>
                <select name="kode_syarat" class="form-control @error('kode_syarat') is-invalid @enderror">
                    <option value="">-- Pilih Kode Profil --</option>
                    @foreach($profileRequirementOptions as $option)
                        <option value="{{ $option['code'] }}" {{ (string) old('kode_syarat', $syarat->kode_syarat) === (string) $option['code'] ? 'selected' : '' }}>
                            {{ $option['label'] }} ({{ $option['code'] }})
                        </option>
                    @endforeach
                </select>
                @error('kode_syarat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="card-footer text-right">
            <a href="{{ route('syarat.index') }}" class="btn btn-secondary mr-2">Batal</a>
            <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
        </div>
    </div>
</form>

