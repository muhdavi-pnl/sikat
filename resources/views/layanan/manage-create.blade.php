<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('layanan.index') }}">Layanan</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Tambahkan layanan baru sekaligus pilih persyaratan yang harus dipenuhi pegawai.</p>

        @include('layanan._manage-form', [
            'action' => route('layanan.store'),
            'submitLabel' => 'Simpan Layanan',
        ])
    </div>
</x-app-layout>

