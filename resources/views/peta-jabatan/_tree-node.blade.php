@php
    $statusClasses = [
        'Terisi' => ['badge' => 'badge-success', 'border' => 'border-top-success', 'icon' => 'fa-check-circle'],
        'Kosong' => ['badge' => 'badge-secondary', 'border' => 'border-top-secondary', 'icon' => 'fa-circle'],
        'Kekurangan' => ['badge' => 'badge-danger', 'border' => 'border-top-danger', 'icon' => 'fa-exclamation-circle'],
        'Kelebihan' => ['badge' => 'badge-warning', 'border' => 'border-top-warning', 'icon' => 'fa-user-plus'],
    ][$node['status']] ?? ['badge' => 'badge-secondary', 'border' => 'border-top-secondary', 'icon' => 'fa-circle'];
    
    $hasChildren = !empty($node['children']);
@endphp

<li class="peta-tree-item" id="node-{{ $node['id'] }}">
    <div class="peta-jabatan-node card shadow-sm {{ $statusClasses['border'] }}">
        <div class="card-body p-3">
            {{-- Header: Status & Kode --}}
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge badge-light font-weight-bold text-muted small">
                    <i class="fas fa-hashtag"></i> {{ $node['kode_jabatan'] ?? 'JBT-' . $node['id'] }}
                </span>
                <span class="badge {{ $statusClasses['badge'] }} px-2 py-1">
                    <i class="fas {{ $statusClasses['icon'] }} mr-1"></i>{{ $node['status'] }}
                </span>
            </div>

            {{-- Nama Jabatan --}}
            <h6 class="font-weight-bold text-dark mb-1 node-title">
                <a href="{{ route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $node['id']]) }}" class="text-dark text-decoration-none hover-primary">
                    {{ $node['nama'] }}
                </a>
            </h6>

            {{-- Unit Kerja --}}
            <div class="small text-muted mb-2">
                <i class="fas fa-building text-primary mr-1"></i>{{ $node['unit_kerja'] }}
            </div>

            {{-- Info Formasi & Pegawai --}}
            <div class="bg-light p-2 rounded mb-2 small">
                <div class="d-flex justify-content-between text-muted{{ $node['kelas_jabatan'] ? ' mb-1' : '' }}">
                    <span><i class="fas fa-users mr-1"></i>Formasi:</span>
                    <span class="font-weight-bold text-dark">{{ $node['jumlah_pemangku'] }} / {{ $node['kebutuhan_pegawai'] }} Orang</span>
                </div>
                @if ($node['kelas_jabatan'])
                    <div class="d-flex justify-content-between text-muted border-top pt-1 mt-1">
                        <span><i class="fas fa-layer-group mr-1"></i>Kelas Jabatan:</span>
                        <span class="font-weight-bold text-dark">{{ $node['kelas_jabatan'] }}</span>
                    </div>
                @endif
            </div>

            {{-- Daftar Pemangku --}}
            <div class="pemangku-section mb-2 small">
                <div class="text-muted mb-1 font-weight-600 d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-user-check text-success mr-1"></i>Pejabat / Pemangku:</span>
                    @if (count($node['pegawais']) > 1)
                        <span class="badge badge-light text-muted px-1 py-0">{{ count($node['pegawais']) }} Orang</span>
                    @endif
                </div>

                @if (count($node['pegawais']) > 1)
                    <div class="pemangku-list-container pr-1" style="{{ count($node['pegawais']) > 3 ? 'max-height: 110px; overflow-y: auto;' : '' }}">
                        <ol class="pl-3 mb-0 text-dark" style="font-size: 0.8rem;">
                            @foreach ($node['pegawais'] as $p)
                                <li class="text-truncate py-0 mb-1" title="{{ $p['nama'] }}">
                                    <span class="font-weight-500">{{ $p['nama'] }}</span>
                                </li>
                            @endforeach
                        </ol>
                    </div>
                @elseif (count($node['pegawais']) === 1)
                    <div class="text-dark font-weight-500 text-truncate py-1" style="font-size: 0.8rem;" title="{{ $node['pegawais'][0]['nama'] }}">
                        <i class="fas fa-user-tie text-info mr-1"></i>{{ $node['pegawais'][0]['nama'] }}
                    </div>
                @else
                    <div class="text-muted font-italic small py-1">
                        <i class="fas fa-user-slash text-warning mr-1"></i>Belum ada pemangku jabatan
                    </div>
                @endif
            </div>

            {{-- Action Buttons & Child Toggle --}}
            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                @if ($hasChildren)
                    <button type="button" class="btn btn-xs btn-outline-dark btn-toggle-children" data-target="#children-{{ $node['id'] }}" title="Buka/Tutup Sub-Jabatan">
                        <i class="fas fa-chevron-down mr-1"></i><span class="child-count">{{ count($node['children']) }}</span> Sub
                    </button>
                @else
                    <span class="text-muted small font-italic"><i class="fas fa-minus mr-1"></i>Staf</span>
                @endif

                <div class="btn-group btn-group-sm">
                    <a href="{{ route('peta-jabatan.manage.show', ['slug' => 'jabatan', 'id' => $node['id']]) }}" class="btn btn-sm btn-outline-info" title="Detail Jabatan">
                        <i class="fas fa-eye"></i>
                    </a>
                    @if ($canManage)
                        <a href="{{ route('peta-jabatan.manage.edit', ['slug' => 'jabatan', 'id' => $node['id']]) }}" class="btn btn-sm btn-outline-primary" title="Edit Jabatan">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="{{ route('peta-jabatan.manage.create', ['slug' => 'jabatan', 'atasan_id' => $node['id'], 'unit_kerja_id' => $node['unit_kerja_id']]) }}" class="btn btn-sm btn-outline-success" title="Tambah Sub-Jabatan / Bawahan">
                            <i class="fas fa-plus"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-jabatan" 
                                data-id="{{ $node['id'] }}" 
                                data-name="{{ $node['nama'] }}" 
                                data-pemangku="{{ $node['jumlah_pemangku'] }}" 
                                data-bawahan="{{ count($node['children']) }}"
                                title="Hapus Jabatan">
                            <i class="fas fa-trash"></i>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($hasChildren)
        <ul class="peta-tree-children" id="children-{{ $node['id'] }}">
            @foreach ($node['children'] as $child)
                @include('peta-jabatan._tree-node', ['node' => $child, 'canManage' => $canManage])
            @endforeach
        </ul>
    @endif
</li>
