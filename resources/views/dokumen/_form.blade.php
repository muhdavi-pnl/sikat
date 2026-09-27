<div class="card-body">
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 pl-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="form-row">
        <div class="form-group col-md-4">
            <label for="kode_dokumen">Kode Dokumen <span class="text-danger">*</span></label>
            <input type="text" name="kode_dokumen" id="kode_dokumen" class="form-control @error('kode_dokumen') is-invalid @enderror" value="{{ old('kode_dokumen', $dokumen->kode_dokumen) }}" required>
            @error('kode_dokumen')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-4">
            <label for="nama_dokumen">Nama Dokumen <span class="text-danger">*</span></label>
            <input type="text" name="nama_dokumen" id="nama_dokumen" class="form-control @error('nama_dokumen') is-invalid @enderror" value="{{ old('nama_dokumen', $dokumen->nama_dokumen) }}" required>
            @error('nama_dokumen')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-4">
            <label for="kategori_pegawai">Peruntukan Status Pegawai <span class="text-danger">*</span></label>
            <select name="kategori_pegawai" id="kategori_pegawai" class="form-control @error('kategori_pegawai') is-invalid @enderror">
                <option value="semua" {{ old('kategori_pegawai', $dokumen->kategori_pegawai ?? 'semua') === 'semua' ? 'selected' : '' }}>Semua Pegawai (PNS & PPPK)</option>
                <option value="pns" {{ old('kategori_pegawai', $dokumen->kategori_pegawai) === 'pns' ? 'selected' : '' }}>Khusus PNS / CPNS</option>
                <option value="pppk" {{ old('kategori_pegawai', $dokumen->kategori_pegawai) === 'pppk' ? 'selected' : '' }}>Khusus PPPK / PPPK Paruh Waktu</option>
            </select>
            @error('kategori_pegawai')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
<div class="card-footer text-right">
    <a href="{{ route('dokumen.index') }}" class="btn btn-outline-secondary mr-2">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

