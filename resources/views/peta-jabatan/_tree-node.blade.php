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
                <div class="d-flex justify-content-between text-muted mb-1">
                    <span><i class="fas fa-users mr-1"></i>Formasi Pegawai:</span>
                    <span class="font-weight-bold text-dark">{{ $node['jumlah_pemangku'] }} / {{ $node['kebutuhan_pegawai'] }} Orang</span>
                </div>
                @if ($node['pangkat_minimal'] || $node['kelas_jabatan'])
                    <div class="d-flex justify-content-between text-muted border-top pt-1 mt-1">
                        @if ($node['kelas_jabatan'])
                            <span>Kelas: <strong>{{ $node['kelas_jabatan'] }}</strong></span>
                        @endif
                        @if ($node['pangkat_minimal'])
                            <span>Min. Pangkat: <strong>{{ $node['pangkat_minimal'] }}</strong></span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Daftar Pemangku --}}
            <div class="pemangku-list mb-2 small">
                @if (!empty($node['pegawais']))
                    <div class="text-muted mb-1 font-weight-600"><i class="fas fa-user-check text-success mr-1"></i>Pejabat / Pemangku:</div>
                    <ul class="list-unstyled mb-0 pl-1">
                        @foreach (array_slice($node['pegawais'], 0, 3) as $p)
                            <li class="text-truncate text-secondary mb-1" title="{{ $p['nama'] }} (NIP: {{ $p['nip'] ?? '-' }})">
                                <i class="fas fa-user-tie text-info mr-1"></i>{{ $p['nama'] }}
                            </li>
                        @endforeach
                        @if (count($node['pegawais']) > 3)
                            <li class="text-muted font-italic small">+{{ count($node['pegawais']) - 3 }} pegawai lainnya</li>
                        @endif
                    </ul>
                @else
                    <div class="text-muted font-italic small">
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
