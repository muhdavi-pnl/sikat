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
            <label for="gedung_id">Gedung <span class="text-danger">*</span></label>
            <select name="gedung_id" id="gedung_id" class="form-control @error('gedung_id') is-invalid @enderror" required>
                <option value="">-- Pilih Gedung --</option>
                @foreach($gedungs as $gedung)
                    <option value="{{ $gedung->id }}" {{ (string) old('gedung_id', $ruang->gedung_id) === (string) $gedung->id ? 'selected' : '' }}>{{ $gedung->nama_gedung }}</option>
                @endforeach
            </select>
            @error('gedung_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-4">
            <label for="kode_ruang">Kode Ruang <span class="text-danger">*</span></label>
            <input type="text" name="kode_ruang" id="kode_ruang" class="form-control @error('kode_ruang') is-invalid @enderror" value="{{ old('kode_ruang', $ruang->kode_ruang) }}" required>
            @error('kode_ruang')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="form-group col-md-4">
            <label for="nama_ruang">Nama Ruang <span class="text-danger">*</span></label>
            <input type="text" name="nama_ruang" id="nama_ruang" class="form-control @error('nama_ruang') is-invalid @enderror" value="{{ old('nama_ruang', $ruang->nama_ruang) }}" required>
            @error('nama_ruang')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="form-group mb-0">
        <label for="keterangan">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="2" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $ruang->keterangan) }}</textarea>
        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="card-footer text-right">
    <a href="{{ route('ruang.index') }}" class="btn btn-outline-secondary mr-2">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

