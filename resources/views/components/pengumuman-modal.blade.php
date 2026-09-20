@php
    $user = auth()->user();
    $activePengumumans = \App\Models\Pengumuman::aktif()
        ->forUser($user)
        ->latest()
        ->get();
    $shouldAutoShow = ! session()->get('pengumuman_popup_dismissed', false) && $activePengumumans->isNotEmpty();
@endphp

@if($activePengumumans->isNotEmpty())
<!-- Modal Pop-up Pengumuman -->
<div class="modal fade" id="pengumumanPopupModal" tabindex="-1" role="dialog" aria-labelledby="pengumumanPopupModalTitle" aria-hidden="true" data-backdrop="static" data-keyboard="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content shadow-lg border-0 overflow-hidden" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-primary text-white py-3" style="background: linear-gradient(135deg, #6777ef 0%, #3549e3 100%);">
                <div class="d-flex align-items-center">
                    <div class="mr-3 bg-white text-primary rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px; font-size: 18px;">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" id="pengumumanPopupModalTitle">
                            Pengumuman
                        </h5>
                        <small class="text-white-50">Sistem Informasi Kepegawaian Terintegrasi (SIKAT)</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-8" data-dismiss="modal" aria-label="Close" style="text-shadow: none;" onclick="dismissPengumumanSession()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                @if($activePengumumans->count() > 1)
                    <!-- Carousel untuk multiple pengumuman -->
                    <div id="pengumumanCarousel" class="carousel slide" data-ride="false" data-interval="false">
                        <div class="carousel-inner">
                            @foreach($activePengumumans as $index => $item)
                                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                    <div class="p-4">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="text-primary font-weight-bold mb-0">{{ $item->judul }}</h4>
                                            <div>
                                                @if($item->tipe === 'teks')
                                                    <span class="badge badge-info"><i class="fas fa-align-left mr-1"></i> Teks</span>
                                                @elseif($item->tipe === 'gambar')
                                                    <span class="badge badge-success"><i class="fas fa-image mr-1"></i> Banner</span>
                                                @else
                                                    <span class="badge badge-warning text-dark"><i class="fas fa-photo-video mr-1"></i> Teks & Gambar</span>
                                                @endif
                                                <span class="badge badge-light border ml-1">{{ $index + 1 }} / {{ $activePengumumans->count() }}</span>
                                            </div>
                                        </div>

                                        @if($item->gambar)
                                            <div class="text-center bg-dark rounded mb-3 p-1 shadow-sm overflow-hidden" style="max-height: 420px;">
                                                <a href="{{ asset($item->gambar) }}" target="_blank" title="Klik untuk memperbesar">
                                                    <img src="{{ asset($item->gambar) }}" alt="{{ $item->judul }}" class="img-fluid rounded" style="max-height: 400px; width: auto; object-fit: contain;">
                                                </a>
                                            </div>
                                        @endif

                                        @if($item->isi)
                                            <div class="announcement-text-content px-2 py-2 text-dark" style="font-size: 15px; line-height: 1.7; white-space: pre-line; max-height: 300px; overflow-y: auto;">
                                                {!! nl2br(e($item->isi)) !!}
                                            </div>
                                        @endif

                                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center text-muted small">
                                            <span><i class="far fa-user mr-1"></i> Diterbitkan oleh: <strong>{{ $item->creator->name ?? 'Admin' }}</strong></span>
                                            <span><i class="far fa-calendar-alt mr-1"></i> {{ $item->created_at ? $item->created_at->format('d M Y') : '' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Carousel Controls -->
                        <div class="px-4 py-2 bg-light border-top d-flex justify-content-between align-items-center">
                            <a class="btn btn-sm btn-outline-primary" href="#pengumumanCarousel" role="button" data-slide="prev">
                                <i class="fas fa-chevron-left mr-1"></i> Sebelumnya
                            </a>
                            <ol class="carousel-indicators position-static mb-0 mx-2" style="position: static; margin-bottom: 0;">
                                @foreach($activePengumumans as $index => $item)
                                    <li data-target="#pengumumanCarousel" data-slide-to="{{ $index }}" class="{{ $index === 0 ? 'active' : '' }} bg-primary" style="width: 24px; height: 5px; border-radius: 3px;"></li>
                                @endforeach
                            </ol>
                            <a class="btn btn-sm btn-outline-primary" href="#pengumumanCarousel" role="button" data-slide="next">
                                Selanjutnya <i class="fas fa-chevron-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                @else
                    @php $item = $activePengumumans->first(); @endphp
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="text-primary font-weight-bold mb-0">{{ $item->judul }}</h4>
                            <div>
                                @if($item->tipe === 'teks')
                                    <span class="badge badge-info"><i class="fas fa-align-left mr-1"></i> Teks</span>
                                @elseif($item->tipe === 'gambar')
                                    <span class="badge badge-success"><i class="fas fa-image mr-1"></i> Banner</span>
                                @else
                                    <span class="badge badge-warning text-dark"><i class="fas fa-photo-video mr-1"></i> Teks & Gambar</span>
                                @endif
                            </div>
                        </div>

                        @if($item->gambar)
                            <div class="text-center bg-dark rounded mb-3 p-1 shadow-sm overflow-hidden" style="max-height: 420px;">
                                <a href="{{ asset($item->gambar) }}" target="_blank" title="Klik untuk memperbesar">
                                    <img src="{{ asset($item->gambar) }}" alt="{{ $item->judul }}" class="img-fluid rounded" style="max-height: 400px; width: auto; object-fit: contain;">
                                </a>
                            </div>
                        @endif

                        @if($item->isi)
                            <div class="announcement-text-content px-2 py-2 text-dark" style="font-size: 15px; line-height: 1.7; white-space: pre-line; max-height: 300px; overflow-y: auto;">
                                {!! nl2br(e($item->isi)) !!}
                            </div>
                        @endif

                        <div class="mt-3 pt-2 border-top d-flex justify-content-between align-items-center text-muted small">
                            <span><i class="far fa-user mr-1"></i> Diterbitkan oleh: <strong>{{ $item->creator->name ?? 'Admin' }}</strong></span>
                            <span><i class="far fa-calendar-alt mr-1"></i> {{ $item->created_at ? $item->created_at->format('d M Y') : '' }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <div class="modal-footer bg-whitesmoke justify-content-between py-2">
                <small class="text-muted"><i class="fas fa-info-circle mr-1"></i> Pop-up ini muncul otomatis setelah login.</small>
                <button type="button" class="btn btn-primary px-4 font-weight-bold" data-dismiss="modal" onclick="dismissPengumumanSession()">
                    <i class="fas fa-check mr-1"></i> Mengerti & Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function dismissPengumumanSession() {
        $.ajax({
            url: "{{ route('pengumuman.dismiss-popup') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                // Session updated
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        @if($shouldAutoShow)
            setTimeout(function() {
                if (typeof $ !== 'undefined' && $('#pengumumanPopupModal').length) {
                    $('#pengumumanPopupModal').modal('show');
                }
            }, 400);
        @endif
    });
</script>
@endif
