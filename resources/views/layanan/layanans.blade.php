<x-app-layout>
    @push('plugins_css')
        <link rel="stylesheet" href="{{ asset('assets/modules/datatables/datatables.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/modules/datatables/dataTables.bootstrap4.min.css') }}">
        <link rel="stylesheet" href="{{ asset('assets/modules/datatables/select.bootstrap4.min.css') }}">
    @endpush

    @section('title', 'Layanan ' . $layanan)
    <x-slot name="header">
        <h1>Layanan</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">Layanan {{ $layanan }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">Layanan {{ $layanan }}</h2>
        <p class="section-lead">
            Semua layanan terkait {{ $layanan }} bisa Anda temukan disini.
        </p>

        <div class="row">
            @foreach($layanans as $layanan)
                <div class="col-12 col-sm-6 col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ $layanan->layanan }}</h4>
                            <div class="card-header-action">
                                <a data-collapse="#layanan-{{ $layanan->id }}" class="btn btn-icon btn-info" href="#">
                                    <i class="fas fa-plus"></i></a>
                            </div>
                        </div>
                        <div class="collapse" id="layanan-{{ $layanan->id }}" style="">
                            <div class="card-body">
                                <div class="section-title mt-0">Persyaratan</div>
                                <ol>
                                    @foreach($layanan->syarat as $layanansyarat)
                                        <li>{{ $layanansyarat->syarat }}</li>
                                    @endforeach
                                </ol>
                            </div>
                            <div class="card-footer text-right">
                                <button onclick="window.location='{{ route('layanan.usul', $layanan->id) }}'" class="btn btn-primary">Buat Usulan</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @push('plugins_js')
        <script src="{{ asset('assets/modules/datatables/datatables.min.js') }}"></script>
        <script src="{{ asset('assets/modules/datatables/dataTables.bootstrap4.min.js') }}"></script>
        <script src="{{ asset('assets/modules/datatables/dataTables.select.min.js') }}"></script>
        <script src="{{ asset('assets/modules/jquery-ui/jquery-ui.min.js') }}"></script>
    @endpush

    @push('page_js')
        <script src="{{ asset('assets/js/page/modules-datatables.js') }}"></script>
    @endpush
</x-app-layout>
