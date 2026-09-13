<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('ruang.index') }}">Ruang</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Perbarui kode, nama, dan lokasi gedung untuk ruang arsip.</p>

        <div class="card">
            <div class="card-header"><h4>Form {{ $title }}</h4></div>
            <form method="POST" action="{{ route('ruang.update', $ruang) }}">
                @csrf
                @method('PUT')
                @include('ruang._form', ['submitLabel' => 'Perbarui'])
            </form>
        </div>
    </div>
</x-app-layout>

