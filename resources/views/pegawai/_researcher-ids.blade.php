@php
    $researcherIds = $researcherIds ?? [];
@endphp

<div class="section-title mb-3">Identitas Akademik</div>
<div class="row" id="profile-researcher-ids">
    @foreach ($researcherIds as $identifier)
        <div class="col-12 col-md-6 mb-3">
            <div class="border rounded h-100 p-3 d-flex align-items-start">
                <div class="mr-3">
                    <span class="btn btn-icon btn-{{ $identifier['color'] }} disabled" aria-hidden="true">
                        <i class="{{ $identifier['icon'] }}"></i>
                    </span>
                </div>
                <div class="flex-grow-1">
                    <div class="text-muted text-small text-uppercase font-weight-bold mb-1">{{ $identifier['label'] }}</div>
                    @if (!empty($identifier['value']))
                        <div class="font-weight-semibold mb-1">{{ $identifier['value'] }}</div>
                        @if (!empty($identifier['url']))
                            <a href="{{ $identifier['url'] }}" target="_blank" rel="noopener noreferrer" class="text-small font-weight-bold" aria-label="Buka profil {{ $identifier['label'] }}">
                                <i class="fas fa-external-link-alt mr-1"></i>Lihat profil
                            </a>
                        @endif
                    @else
                        <i class="text-secondary text-small">--No Data--</i>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

