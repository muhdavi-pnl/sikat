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
        <h2 class="section-title">Edit Pengumuman</h2>
        <p class="section-lead">Perbarui konten teks, file gambar, target penerima, atau periode berlaku pengumuman.</p>

        <div class="row">
            <div class="col-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h4>Formulir Edit Pengumuman</h4>
                    </div>
                    <form action="{{ route('pengumuman.update', ['pengumuman' => $pengumuman]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        @include('pengumuman._form', ['submitLabel' => 'Perbarui Pengumuman'])
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
