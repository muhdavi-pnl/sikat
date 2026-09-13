@php
    $statusBadge = [
        'Terisi' => 'badge-success',
        'Kosong' => 'badge-secondary',
        'Kelebihan' => 'badge-warning',
        'Kekurangan' => 'badge-danger',
    ][$node['status']] ?? 'badge-secondary';
@endphp
<li>
    <div class="peta-jabatan-node card d-inline-block mb-2">
        <div class="card-body py-2 px-3">
            <div class="d-flex justify-content-between align-items-center">
                <strong>{{ $node['nama'] }}</strong>
                <span class="badge {{ $statusBadge }} ml-2">{{ $node['status'] }}</span>
            </div>
            <div class="text-muted small">{{ $node['unit_kerja'] }}</div>
            <div class="small">
                Pemangku: {{ $node['jumlah_pemangku'] }} / Kebutuhan: {{ $node['kebutuhan_pegawai'] }}
            </div>
            @if (!empty($node['pemangku']))
                <div class="small text-muted">{{ implode(', ', $node['pemangku']) }}</div>
            @endif
        </div>
    </div>

    @if (!empty($node['children']))
        <ul>
            @foreach ($node['children'] as $child)
                @include('peta-jabatan._tree-node', ['node' => $child])
            @endforeach
        </ul>
    @endif
</li>
