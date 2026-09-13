<x-app-layout>
    @section('title', $title)
    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item active"><a href="{{ route('syarat.index') }}">Syarat</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Atur mapping syarat ke dokumen wajib, field profil, atau verifikasi manual.</p>

        @include('syarat._form', [
            'action' => route('syarat.store'),
            'submitLabel' => 'Simpan Syarat',
        ])
    </div>

    @push('page_js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const mappingSource = document.getElementById('mapping_source');
                const documentGroup = document.getElementById('document-mapping-group');
                const profileGroup = document.getElementById('profile-mapping-group');

                function toggleMappingFields() {
                    const value = mappingSource.value;
                    documentGroup.classList.toggle('d-none', value !== 'document');
                    profileGroup.classList.toggle('d-none', value !== 'profile');
                }

                mappingSource.addEventListener('change', toggleMappingFields);
                toggleMappingFields();
            });
        </script>
    @endpush
</x-app-layout>

