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

    <div class="form-group">
        <label for="nama_gedung">Nama Gedung <span class="text-danger">*</span></label>
        <input type="text" name="nama_gedung" id="nama_gedung" class="form-control @error('nama_gedung') is-invalid @enderror" value="{{ old('nama_gedung', $gedung->nama_gedung) }}" required>
        @error('nama_gedung')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="alamat_gedung">Alamat Gedung</label>
        <textarea name="alamat_gedung" id="alamat_gedung" rows="3" class="form-control @error('alamat_gedung') is-invalid @enderror">{{ old('alamat_gedung', $gedung->alamat_gedung) }}</textarea>
        @error('alamat_gedung')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="form-group mb-0">
        <label for="keterangan">Keterangan</label>
        <textarea name="keterangan" id="keterangan" rows="2" class="form-control @error('keterangan') is-invalid @enderror">{{ old('keterangan', $gedung->keterangan) }}</textarea>
        @error('keterangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
<div class="card-footer text-right">
    <a href="{{ route('gedung.index') }}" class="btn btn-outline-secondary mr-2">Batal</a>
    <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
</div>

