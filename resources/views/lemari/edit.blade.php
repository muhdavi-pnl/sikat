<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('lemari.index') }}">Lemari</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Perbarui penempatan ruang dan informasi lemari arsip.</p>

        <div class="card">
            <div class="card-header"><h4>Form {{ $title }}</h4></div>
            <form method="POST" action="{{ route('lemari.update', $lemari) }}">
                @csrf
                @method('PUT')
                @include('lemari._form', ['submitLabel' => 'Perbarui'])
            </form>
        </div>
    </div>
</x-app-layout>

