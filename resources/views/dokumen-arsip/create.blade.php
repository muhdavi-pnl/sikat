<x-app-layout>
    @push('plugins_css')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    @endpush

    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('dokumen.index') }}">{{ $title }}</a></div>
            <div class="breadcrumb-item">Unggah Dokumen</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">Unggah Dokumen</h2>
        <p class="section-lead">
            Unggah dokumen yang Anda miliki segera ke SIKAT demi kemudahan di masa depan.
        </p>

        <div class="row">
            <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Form Unggah Dokumen</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('dokumen-arsip.store') }}" enctype="multipart/form-data" method="post">
                            @csrf
                            <div class="form-group">
                                <label>Pilih Jenis Dokumen</label>
                                <select class="form-control select2" name="dokumen_id" required autofocus>
                                    <option value="-1">Pilih salah satu...</option>
                                    @foreach($dokumens as $dokumen)
                                        <option value="{{ $dokumen->id }}">{{ $dokumen->nama_dokumen }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Pilih Dokumen</label>
                                <div class="custom-file">
                                    <input type="file" name="file" class="custom-file-input" id="customFile">
                                    <label class="custom-file-label" for="customFile">Choose file</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Nomor Dokumen</label>
                                <input type="text" class="form-control" name="nomor">
                            </div>
                            <div class="form-group">
                                <label for="tanggal">Tanggal Dokumen</label>
                                <input type="date" class="form-control" id="tanggal" name="tanggal" aria-describedby="tanggal">
                                <small id="tanggal" class="form-text text-muted">
                                     Pilih tanggal yang terpenting dari dokumen Anda, misalnya: TMT, Tanggal Lulus, dll.
                                </small>
                            </div>
                            <div class="card-footer text-right">
                                <button class="btn btn-primary mr-1" type="submit">Simpan</button>
                                <a class="btn btn-secondary" href="{{ route('dokumen-arsip.index') }}">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @push('plugins_js')
            <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
        @endpush

        @push('page_js')
            <script type="text/javascript">
                $(document).ready(function () {
                    bsCustomFileInput.init()
                });
            </script>
        @endpush
</x-app-layout>


