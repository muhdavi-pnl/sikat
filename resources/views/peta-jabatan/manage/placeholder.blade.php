<x-app-layout>
    @section('title', $title)

    <x-slot name="header">
        <h1>{{ $title }}</h1>
    </x-slot>

    <div class="section-body">
        <div class="alert alert-info mb-0">
            {{ $title }}@isset($recordId) #{{ $recordId }}@endisset belum tersedia.
        </div>
    </div>
</x-app-layout>
