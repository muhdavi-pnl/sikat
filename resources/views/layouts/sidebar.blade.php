<ul class="sidebar-menu">
    <li class="menu-header">Dashboard</li>
    <li class="{{ (request()->routeIs('dashboard') || (request()->is('dashboard') && !request()->is('dashboard/statistik*'))) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-tachometer-alt"></i> <span>{{ (auth()->user() && auth()->user()->hasAnyRole(['pimpinan', 'super-admin', 'kepegawaian'])) ? 'Dashboard Pimpinan' : 'Dashboard Pegawai' }}</span>
        </a>
    </li>
    <li class="{{ (request()->is('dashboard/statistik*') || request()->routeIs('dashboard.statistik')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard.statistik') }}">
            <i class="fas fa-chart-pie"></i> <span>Statistik Pegawai</span>
        </a>
    </li>

    <li class="menu-header">Pegawai</li>
    <li class="{{ (request()->is('pegawai/profile')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('pegawai.profile') }}">
            <i class="fas fa-user"></i> <span>Profil</span>
        </a>
    </li>
    <li class="{{ (request()->is('pegawai/dokumen')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('pegawai.dokumen') }}">
            <i class="fas fa-file-alt"></i> <span>Dokumen</span>
        </a>
    </li>
    <li class="{{ (request()->is('pegawai/layanan')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('pegawai.layanan') }}">
            <i class="fas fa-list"></i> <span>Layanan</span>
        </a>
    </li>

    <li class="menu-header">Layanan</li>
    <li class="{{ (request()->is('layanan/cuti')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('layanan.cuti') }}">
            <i class="fas fa-calendar-alt"></i> <span>Layanan Cuti</span>
        </a>
    </li>
    <li class="{{ (request()->is('layanan/fungsional')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('layanan.fungsional') }}">
            <i class="fas fa-th"></i> <span>Layanan Fungsional</span>
        </a>
    </li>
    <li class="{{ (request()->is('layanan/kepegawaian')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('layanan.kepegawaian') }}">
            <i class="fas fa-th-large"></i> <span>Layanan Kepegawaian</span>
        </a>
    </li>

    @php
        $sidebarUser = auth()->user();
        $sidebarPegawai = $sidebarUser?->pegawai;
        $hasSubordinates = $sidebarPegawai && count(app(\App\Services\CutiService::class)->getSubordinatePegawaiIds($sidebarPegawai)) > 0;
        $isPybmc = app(\App\Services\CutiService::class)->isUserDesignatedPybmc($sidebarUser);
        $isSuperOrKepegawaian = $sidebarUser && $sidebarUser->hasAnyRole(['super-admin', 'kepegawaian']);
        $isAtasanRole = $sidebarUser && $sidebarUser->hasRole('atasan');
    @endphp

    @if($hasSubordinates || $isPybmc || $isSuperOrKepegawaian || $isAtasanRole)
    <li class="menu-header">Persetujuan Cuti</li>
    @if($hasSubordinates || $isSuperOrKepegawaian || $isAtasanRole)
    <li class="{{ (request()->is('cuti-approval/atasan*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('cuti.approval.atasan.index') }}">
            <i class="fas fa-user-check"></i> <span>Persetujuan Atasan</span>
        </a>
    </li>
    @endif
    @if($isPybmc || $isSuperOrKepegawaian)
    <li class="{{ (request()->is('cuti-approval/pybmc*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('cuti.approval.pybmc.index') }}">
            <i class="fas fa-gavel"></i> <span>Persetujuan PYBMC</span>
        </a>
    </li>
    @endif
    @endif


    @hasanyrole('super-admin|kepegawaian')
    <li class="menu-header">Kepegawaian</li>
    <li class="{{ (request()->is('kepegawaian/pegawai*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('kepegawaian.pegawai') }}">
            <i class="fas fa-users"></i> <span>Pegawai</span>
        </a>
    </li>
    <li class="{{ (request()->is('kepegawaian/pengguna*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('kepegawaian.pengguna') }}">
            <i class="fas fa-users-cog"></i> <span>Pengguna</span>
        </a>
    </li>
    <li class="{{ (request()->is('kepegawaian/dokumen*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('kepegawaian.dokumen.index') }}">
            <i class="fas fa-file-signature"></i> <span>Review Dokumen</span>
        </a>
    </li>
    <li class="{{ (request()->is('kepegawaian/layanan*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('kepegawaian.layanan.proses') }}">
            <i class="fas fa-concierge-bell"></i> <span>Proses Layanan</span>
        </a>
    </li>
    <li class="{{ (request()->is('kepegawaian/cuti*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('kepegawaian.cuti.proses') }}">
            <i class="fas fa-calendar-alt"></i> <span>Proses Cuti</span>
        </a>
    </li>
    <li class="{{ (request()->is('kepegawaian/studi-lanjut*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('kepegawaian.studi-lanjut.index') }}">
            <i class="fas fa-user-graduate"></i> <span>Studi Lanjut</span>
        </a>
    </li>
    <li class="{{ (request()->is('pengumuman*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('pengumuman.index') }}">
            <i class="fas fa-bullhorn"></i> <span>Pengumuman</span>
        </a>
    </li>
    <li class="dropdown {{ (request()->is('admin/arsip*')) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-archive"></i> <span>Arsip</span></a>
        <ul class="dropdown-menu">
            <li class="{{ (request()->is('admin/arsip/gedung*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('gedung.index') }}">
                    <i class="fas fa-building"></i> <span>Gedung</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/arsip/ruang*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('ruang.index') }}">
                    <i class="fas fa-door-open"></i> <span>Ruang</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/arsip/lemari*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('lemari.index') }}">
                    <i class="fas fa-archive"></i> <span>Lemari</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/arsip/rak*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('rak.index') }}">
                    <i class="fas fa-layer-group"></i> <span>Rak</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/arsip/lokasi-arsip*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('lokasi-arsip.index') }}">
                    <i class="fas fa-map-marker-alt"></i> <span>Lokasi Arsip</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/arsip/dokumen*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('dokumen.index') }}">
                    <i class="fas fa-file-alt"></i> <span>Dokumen</span>
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown {{ (request()->is('admin/layanan*')) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-wrench"></i> <span>Layanan</span></a>
        <ul class="dropdown-menu">
            <li class="{{ (request()->is('admin/layanan/layanan*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('layanan.index') }}"><i class="fas fa-concierge-bell"></i> <span>Layanan</span></a>
            </li>
            <li class="{{ (request()->is('admin/layanan/syarat*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('syarat.index') }}"><i class="fas fa-clipboard-check"></i> <span>Syarat</span></a>
            </li>
        </ul>
    </li>

    <li class="menu-header">Jabatan</li>
    <li class="{{ (request()->is('peta-jabatan/manage/jabatan*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('peta-jabatan.manage.index', ['slug' => 'jabatan']) }}">
            <i class="fas fa-user-tie"></i> <span>Kelola Jabatan</span>
        </a>
    </li>
    <li class="{{ (request()->routeIs('peta-jabatan.index') || (request()->is('peta-jabatan*') && !request()->is('peta-jabatan/manage*'))) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('peta-jabatan.index') }}">
            <i class="fas fa-project-diagram"></i> <span>Peta Jabatan</span>
        </a>
    </li>
    <li class="{{ (request()->is('peta-jabatan/manage/career-path*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('peta-jabatan.manage.index', ['slug' => 'career-path']) }}">
            <i class="fas fa-route"></i> <span>Career Path</span>
        </a>
    </li>
    @endhasanyrole

    @role('pimpinan')
    @unlessrole('super-admin|kepegawaian')
    <li class="menu-header">Jabatan</li>
    <li class="{{ (request()->routeIs('peta-jabatan.index') || (request()->is('peta-jabatan*') && !request()->is('peta-jabatan/manage*'))) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('peta-jabatan.index') }}">
            <i class="fas fa-project-diagram"></i> <span>Peta Jabatan</span>
        </a>
    </li>
    @endunlessrole
    @endrole

    @role('super-admin')
    <li class="menu-header">Master Data</li>
    <li class="dropdown {{ (request()->is('admin/pengguna*')) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-user-lock"></i>
            <span>Hak Akses</span>
        </a>
        <ul class="dropdown-menu">
            <li class="{{ (request()->is('admin/pengguna/role*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('role.index') }}">
                    <i class="fas fa-user-shield"></i> <span>Peran</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/pengguna/permission*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('permission.index') }}">
                    <i class="fas fa-key"></i> <span>Izin Akses</span>
                </a>
            </li>
        </ul>
    </li>
    <li class="dropdown {{ (request()->is('admin/referensi*')) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown"><i class="fas fa-pencil-ruler"></i>
            <span>Referensi</span>
        </a>
        <ul class="dropdown-menu">
            <li class="{{ (request()->is('admin/referensi/agama*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('agama.index') }}">
                    <i class="fas fa-book-open"></i> <span>Agama</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/referensi/jabatan*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('jabatan.index') }}">
                    <i class="fas fa-user-tie"></i> <span>Jabatan</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/referensi/pangkat*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('pangkat.index') }}">
                    <i class="fas fa-medal"></i> <span>Pangkat</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/referensi/pendidikan*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('pendidikan.index') }}">
                    <i class="fas fa-graduation-cap"></i> <span>Pendidikan</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/referensi/unit-kerja*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('unit-kerja.index') }}">
                    <i class="fas fa-sitemap"></i> <span>Unit Kerja</span>
                </a>
            </li>
            <li class="{{ (request()->is('admin/referensi/program-studi*')) ? 'active' : '' }}">
                <a class="nav-link" href="{{ route('program-studi.index') }}">
                    <i class="fas fa-university"></i> <span>Program Studi</span>
                </a>
            </li>
        </ul>
    </li>
    <li class="{{ (request()->is('admin/forensics*')) ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.forensics.audit-logs.index') }}">
            <i class="fas fa-shield-alt"></i> <span>Log Audit</span>
        </a>
    </li>
    @endrole
</ul>

<div class="mt-4 mb-4 p-3 hide-sidebar-mini">
    <a href="https://youtu.be/pnUw1ENk8cA" target="_blank" class="btn btn-primary btn-lg btn-block btn-icon-split">
        <i class="fas fa-book"></i> Buku Panduan
    </a>
    <a href="https://youtu.be/pnUw1ENk8cA" target="_blank" class="btn btn-primary btn-lg btn-block btn-icon-split">
        <i class="fab fa-youtube"></i> Video Tutorial
    </a>
</div>
