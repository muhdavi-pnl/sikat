<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item"><a href="{{ route('pengumuman.index') }}">Pengumuman</a></div>
            <div class="breadcrumb-item active">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">Buat Pengumuman Baru</h2>
        <p class="section-lead">Isi form di bawah ini untuk membuat pengumuman baru berbasis teks atau unggah gambar.</p>

        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Formulir Pengumuman</h4>
                    </div>
                    <form action="{{ route('pengumuman.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @include('pengumuman._form', ['submitLabel' => 'Simpan Pengumuman'])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
