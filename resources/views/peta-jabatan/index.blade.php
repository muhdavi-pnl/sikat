<x-app-layout>
    @section('title', $title)

    <x-slot name="header">
        <h1>{{ $title }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ route('dashboard') }}">Dashboard</a></div>
            <div class="breadcrumb-item">{{ $title }}</div>
        </div>
    </x-slot>

    <div class="section-body">
        <h2 class="section-title">{{ $title }}</h2>
        <p class="section-lead">Master data jabatan terintegrasi untuk struktur organisasi, gap kebutuhan, dan career path.</p>

        @if ($canManage)
            <div class="mb-3">
                <a href="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}" class="btn btn-primary btn-icon icon-left">
                    <i class="fas fa-cog"></i> Kelola Jabatan
                </a>
                <a href="{{ route('peta-jabatan.manage.index', ['slug' => 'career-path']) }}" class="btn btn-secondary btn-icon icon-left">
                    <i class="fas fa-route"></i> Kelola Career Path
                </a>
            </div>
        @else
            <div class="alert alert-info"><i class="fas fa-eye"></i> Anda memiliki akses lihat saja (read-only) pada modul ini.</div>
        @endif

        <div class="row" id="peta-jabatan-summary-cards">
            @foreach ($summary as $label => $value)
                <div class="col-lg-3 col-md-6 col-12">
                    <div class="card card-statistic-1">
                        <div class="card-icon bg-primary"><i class="fas fa-sitemap"></i></div>
                        <div class="card-wrap">
                            <div class="card-header"><h4>{{ ucwords(str_replace('_', ' ', $label)) }}</h4></div>
                            <div class="card-body">{{ $value }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form class="row g-2">
                    <div class="col-md-4"><input type="text" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="Cari jabatan"></div>
                    <div class="col-md-3">
                        <select name="unit_kerja_id" class="form-control">
                            <option value="">Semua unit kerja</option>
                            @foreach ($unitKerjas as $unitKerja)
                                <option value="{{ $unitKerja->id }}" @selected($filters['unit_kerja_id'] == $unitKerja->id)>{{ $unitKerja->unit_kerja }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="jenis_jabatan_id" class="form-control">
                            <option value="">Semua jenis jabatan</option>
                            @foreach ($jenisJabatans as $jenisJabatan)
                                <option value="{{ $jenisJabatan->id }}" @selected($filters['jenis_jabatan_id'] == $jenisJabatan->id)>{{ $jenisJabatan->jenis_jabatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="status_jabatan" value="{{ $filters['status_jabatan'] }}" class="form-control" placeholder="Status">
                    </div>
                    <div class="col-md-12 text-right">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Terapkan Filter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- <div class="card">
            <div class="card-header"><h4>Organizational Chart</h4></div>
            <div class="card-body">
                @if (empty($tree))
                    <p class="text-muted mb-0">Belum ada data jabatan untuk ditampilkan.</p>
                @else
                    <ul class="peta-jabatan-tree list-unstyled pl-0">
                        @foreach ($tree as $node)
                            @include('peta-jabatan._tree-node', ['node' => $node])
                        @endforeach
                    </ul>
                @endif
            </div>
        </div> --}}

        <div class="card mt-3">
            <div class="card-header"><h4>Analisis Kebutuhan Jabatan</h4></div>
            <div class="card-body table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Jabatan</th>
                            <th>Unit Kerja</th>
                            <th class="text-end">Kebutuhan</th>
                            <th class="text-end">Terisi</th>
                            <th class="text-end">Kekurangan</th>
                            <th class="text-end">Kelebihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($gapRows as $row)
                            <tr>
                                <td>{{ $row['jabatan'] }}</td>
                                <td>{{ $row['unit_kerja'] }}</td>
                                <td class="text-end">{{ $row['kebutuhan'] }}</td>
                                <td class="text-end">{{ $row['terisi'] }}</td>
                                <td class="text-end">{{ $row['kekurangan'] }}</td>
                                <td class="text-end">{{ $row['kelebihan'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="table-empty-row">Belum ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h4>Distribusi &amp; Kebutuhan Pengisian (Dashboard API)</h4></div>
            <div class="card-body">
                <div id="peta-jabatan-dashboard-loading" class="text-muted">Memuat data dashboard...</div>
                <div id="peta-jabatan-dashboard-content" class="d-none">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Distribusi Dosen per Jabatan Akademik</h5>
                            <ul id="peta-jabatan-dosen-list" class="list-group mb-3"></ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Jabatan yang Membutuhkan Pengisian</h5>
                            <ul id="peta-jabatan-pengisian-list" class="list-group mb-3"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('page_js')
        <script>
            (function () {
                var loading = document.getElementById('peta-jabatan-dashboard-loading');
                var content = document.getElementById('peta-jabatan-dashboard-content');
                var dosenList = document.getElementById('peta-jabatan-dosen-list');
                var pengisianList = document.getElementById('peta-jabatan-pengisian-list');

                fetch('{{ route('peta-jabatan.dashboard') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    credentials: 'same-origin',
                })
                    .then(function (response) { return response.json(); })
                    .then(function (payload) {
                        var data = payload.data || {};

                        (data.distribusi_dosen_per_jabatan_akademik || []).forEach(function (item) {
                            var li = document.createElement('li');
                            li.className = 'list-group-item d-flex justify-content-between align-items-center';
                            li.textContent = item.label;
                            var badge = document.createElement('span');
                            badge.className = 'badge badge-primary badge-pill';
                            badge.textContent = item.total;
                            li.appendChild(badge);
                            dosenList.appendChild(li);
                        });

                        var pengisian = data.jabatan_membutuhkan_pengisian || [];
                        if (pengisian.length === 0) {
                            var empty = document.createElement('li');
                            empty.className = 'list-group-item text-muted';
                            empty.textContent = 'Tidak ada jabatan yang kekurangan pegawai.';
                            pengisianList.appendChild(empty);
                        } else {
                            pengisian.forEach(function (item) {
                                var li = document.createElement('li');
                                li.className = 'list-group-item d-flex justify-content-between align-items-center';
                                li.textContent = item.jabatan + ' (' + item.unit_kerja + ')';
                                var badge = document.createElement('span');
                                badge.className = 'badge badge-danger badge-pill';
                                badge.textContent = 'Kurang ' + item.kekurangan;
                                li.appendChild(badge);
                                pengisianList.appendChild(li);
                            });
                        }

                        loading.classList.add('d-none');
                        content.classList.remove('d-none');
                    })
                    .catch(function () {
                        loading.textContent = 'Gagal memuat data dashboard.';
                    });
            })();
        </script>
    @endpush
</x-app-layout>

