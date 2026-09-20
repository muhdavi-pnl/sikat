<?php

use App\Http\Controllers\Front\AgamaController;
use App\Http\Controllers\Front\ArsipController;
use App\Http\Controllers\Front\AuditLogController;
use App\Http\Controllers\Front\CutiApprovalController;
use App\Http\Controllers\Front\DashboardController;
use App\Http\Controllers\Front\DokumenController;
use App\Http\Controllers\Front\GedungController;
use App\Http\Controllers\Front\JabatanController;
use App\Http\Controllers\Front\KepegawaianDokumenPegawaiController;
use App\Http\Controllers\Front\LandingController;
use App\Http\Controllers\Front\LayananController;
use App\Http\Controllers\Front\LemariController;
use App\Http\Controllers\Front\LokasiArsipController;
use App\Http\Controllers\Front\PangkatController;
use App\Http\Controllers\Front\PegawaiController;
use App\Http\Controllers\Front\PendidikanController;
use App\Http\Controllers\Front\PermissionController;
use App\Http\Controllers\Front\PetaJabatanController;
use App\Http\Controllers\Front\PengumumanController;
use App\Http\Controllers\Front\ProgramStudiController;
use App\Http\Controllers\Front\RakController;
use App\Http\Controllers\Front\RoleController;
use App\Http\Controllers\Front\RuangController;
use App\Http\Controllers\Front\StatistikPegawaiController;
use App\Http\Controllers\Front\StudiLanjutController;
use App\Http\Controllers\Front\SyaratController;
use App\Http\Controllers\Front\UnitKerjaController;
use App\Http\Controllers\Front\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

require __DIR__.'/auth.php';

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::middleware(['auth', 'password.changed'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard/statistik', [StatistikPegawaiController::class, 'index'])->name('dashboard.statistik');

    Route::prefix('wilayah')->group(function () {
        Route::get('provinsis', [PegawaiController::class, 'domisiliProvinsis'])->name('pegawai.wilayah.provinsis');
        Route::get('kabupatens', [PegawaiController::class, 'domisiliKabupatens'])->name('pegawai.wilayah.kabupatens');
        Route::get('kecamatans', [PegawaiController::class, 'domisiliKecamatans'])->name('pegawai.wilayah.kecamatans');
        Route::get('kelurahans', [PegawaiController::class, 'domisiliKelurahans'])->name('pegawai.wilayah.kelurahans');
    });

    Route::prefix('pegawai')->group(function () {
        Route::get('/detach/{dokumen_id}/{pegawai_id}', [ArsipController::class, 'detach'])->name('arsip.detach');
        Route::get('/download/{file_name}/{pegawai_nip}', [ArsipController::class, 'download'])->name('arsip.download');
        Route::resource('arsip', ArsipController::class);
        Route::get('layanan/usul/{id}', [LayananController::class, 'usul'])->name('layanan.usul');
        Route::get('layanan/{layanan}/review', [PegawaiController::class, 'reviewLayananUsulan'])->name('pegawai.layanan.review');
        Route::get('layanan/{layananPegawai}/cetak-formulir-cuti', [PegawaiController::class, 'printLayananCuti'])->name('pegawai.layanan.cuti.print');
        Route::get('profile', [PegawaiController::class, 'profile'])->name('pegawai.profile');
        Route::put('profile', [PegawaiController::class, 'updateProfile'])->name('pegawai.profile.update');
        Route::put('profile/password', [PegawaiController::class, 'updatePassword'])->name('pegawai.profile.password.update');
        Route::get('dokumen', [PegawaiController::class, 'dokumen'])->name('pegawai.dokumen');
        Route::post('dokumen', [PegawaiController::class, 'storeDokumen'])->name('pegawai.dokumen.store');
        Route::get('layanan', [PegawaiController::class, 'layanan'])->name('pegawai.layanan');
        Route::post('layanan/{layanan}/review', [PegawaiController::class, 'previewLayananUsulan'])->name('pegawai.layanan.preview');
        Route::post('layanan/{layanan}/usul', [PegawaiController::class, 'storeLayananUsulan'])->name('pegawai.layanan.store');
        Route::get('layanan/{layananPegawai}/selesai', [PegawaiController::class, 'doneLayananUsulan'])->name('pegawai.layanan.done');

        Route::middleware(['role:super-admin|kepegawaian'])->group(function () {
            Route::resource('pegawai', PegawaiController::class);
        });
    });

    // Peta Jabatan: viewable by Super Admin, Administrator Kepegawaian, and
    // Pimpinan (per docs/peta-jabatan.md section 10). Dosen/Pegawai only see
    // their own jabatan via the existing pegawai.profile page.
    Route::middleware(['role:super-admin|kepegawaian|pimpinan'])->prefix('peta-jabatan')->group(function () {
        Route::get('/', [PetaJabatanController::class, 'index'])->name('peta-jabatan.index');
        Route::get('/dashboard', [PetaJabatanController::class, 'dashboard'])->name('peta-jabatan.dashboard');
    });

    // Jabatan (and career path) management is shared by Super Admin and
    // Administrator Kepegawaian (docs/peta-jabatan.md section 10). This uses
    // a distinct URI namespace from admin/crud/{slug} on purpose: Laravel's
    // route collection keys routes by method+URI template, so registering a
    // second {slug} route on the exact same "admin/crud/{slug}" template
    // would silently replace the first one for every slug (not just the
    // ones constrained by where()), regardless of registration order.
    Route::middleware(['role:super-admin|kepegawaian'])
        ->prefix('peta-jabatan/manage')
        ->where(['slug' => 'jabatan|career-path'])
        ->group(function () {
            Route::get('{slug}', [\App\Http\Controllers\Front\AdminCrudController::class, 'index'])->name('peta-jabatan.manage.index');
            Route::get('{slug}/create', [\App\Http\Controllers\Front\AdminCrudController::class, 'create'])->name('peta-jabatan.manage.create');
            Route::post('{slug}', [\App\Http\Controllers\Front\AdminCrudController::class, 'store'])->name('peta-jabatan.manage.store');
            Route::get('{slug}/{id}', [\App\Http\Controllers\Front\AdminCrudController::class, 'show'])->whereNumber('id')->name('peta-jabatan.manage.show');
            Route::get('{slug}/{id}/edit', [\App\Http\Controllers\Front\AdminCrudController::class, 'edit'])->whereNumber('id')->name('peta-jabatan.manage.edit');
            Route::put('{slug}/{id}', [\App\Http\Controllers\Front\AdminCrudController::class, 'update'])->whereNumber('id')->name('peta-jabatan.manage.update');
            Route::delete('{slug}/{id}', [\App\Http\Controllers\Front\AdminCrudController::class, 'destroy'])->whereNumber('id')->name('peta-jabatan.manage.destroy');
        });

    Route::prefix('layanan')->group(function () {
        Route::get('usulan', [LayananController::class, 'usulan'])->name('layanan.usulan');
        Route::get('fungsional', [LayananController::class, 'fungsional'])->name('layanan.fungsional');
        Route::get('kepegawaian', [LayananController::class, 'kepegawaian'])->name('layanan.kepegawaian');
        Route::get('cuti', [LayananController::class, 'cuti'])->name('layanan.cuti');
    });

    Route::prefix('cuti-approval')->group(function () {
        Route::get('atasan', [CutiApprovalController::class, 'indexAtasan'])->name('cuti.approval.atasan.index');
        Route::get('atasan/{layananPegawai}', [CutiApprovalController::class, 'showAtasan'])->name('cuti.approval.atasan.show');
        Route::post('atasan/{layananPegawai}', [CutiApprovalController::class, 'approveAtasan'])->name('cuti.approval.atasan.approve');

        Route::get('pybmc', [CutiApprovalController::class, 'indexPybmc'])->name('cuti.approval.pybmc.index');
        Route::get('pybmc/{layananPegawai}', [CutiApprovalController::class, 'showPybmc'])->name('cuti.approval.pybmc.show');
        Route::post('pybmc/{layananPegawai}', [CutiApprovalController::class, 'approvePybmc'])->name('cuti.approval.pybmc.approve');
    });

    Route::prefix('kepegawaian')->group(function () {
        Route::middleware(['role:super-admin|kepegawaian'])->group(function () {
            Route::get('dokumen', [KepegawaianDokumenPegawaiController::class, 'index'])->name('kepegawaian.dokumen.index');
            Route::get('dokumen/{pegawai}', [KepegawaianDokumenPegawaiController::class, 'show'])->name('kepegawaian.dokumen.show');
            Route::post('dokumen/{pegawai}', [KepegawaianDokumenPegawaiController::class, 'store'])->name('kepegawaian.dokumen.store');
            Route::get('dokumen/{pegawai}/{dokumen}/edit', [KepegawaianDokumenPegawaiController::class, 'edit'])->name('kepegawaian.dokumen.edit');
            Route::put('dokumen/{pegawai}/{dokumen}', [KepegawaianDokumenPegawaiController::class, 'update'])->name('kepegawaian.dokumen.update');

            Route::get('layanan', [PegawaiController::class, 'indexLayananProses'])->name('kepegawaian.layanan.proses');
            Route::get('layanan/{layananPegawai}/edit', [PegawaiController::class, 'editLayananProses'])->name('kepegawaian.layanan.edit');
            Route::put('layanan/{layananPegawai}', [PegawaiController::class, 'updateLayananProses'])->name('kepegawaian.layanan.update');

            Route::get('cuti', [PegawaiController::class, 'indexCutiProses'])->name('kepegawaian.cuti.proses');
            Route::get('cuti/pejabat-berwenang', [CutiApprovalController::class, 'pybmcSetting'])->name('kepegawaian.cuti.pybmc-setting');
            Route::post('cuti/pejabat-berwenang', [CutiApprovalController::class, 'updatePybmcSetting'])->name('kepegawaian.cuti.pybmc-setting.update');
            Route::get('cuti/{layananPegawai}/edit', [PegawaiController::class, 'editCutiProses'])->name('kepegawaian.cuti.edit');
            Route::put('cuti/{layananPegawai}', [PegawaiController::class, 'updateLayananProses'])->name('kepegawaian.cuti.update');

            Route::get('studi-lanjut/export', [StudiLanjutController::class, 'export'])->name('kepegawaian.studi-lanjut.export');
            Route::get('studi-lanjut/options/pegawais', [StudiLanjutController::class, 'searchPegawais'])->name('kepegawaian.studi-lanjut.options.pegawais');
            Route::resource('studi-lanjut', StudiLanjutController::class)->names('kepegawaian.studi-lanjut');

            Route::get('pegawai', [PegawaiController::class, 'index'])->name('kepegawaian.pegawai');
            Route::get('pegawai/export', [PegawaiController::class, 'export'])->name('kepegawaian.pegawai.export');
            Route::get('pegawai/print', [PegawaiController::class, 'print'])->name('kepegawaian.pegawai.print');
            Route::get('pegawai/options/users', [PegawaiController::class, 'searchUsers'])->name('kepegawaian.pegawai.options.users');
            Route::get('pegawai/options/jabatans', [PegawaiController::class, 'searchJabatans'])->name('kepegawaian.pegawai.options.jabatans');
            Route::get('pegawai/options/program-studis', [PegawaiController::class, 'searchProgramStudis'])->name('kepegawaian.pegawai.options.program-studis');
            Route::get('pegawai/options/kelurahans', [PegawaiController::class, 'searchKelurahans'])->name('kepegawaian.pegawai.options.kelurahans');
            Route::get('pegawai/create', [PegawaiController::class, 'create'])->name('kepegawaian.pegawai.create');
            Route::post('pegawai', [PegawaiController::class, 'store'])->name('kepegawaian.pegawai.store');
            Route::get('pegawai/{pegawai}', [PegawaiController::class, 'show'])->name('kepegawaian.pegawai.show');
            Route::get('pegawai/{pegawai}/edit', [PegawaiController::class, 'edit'])->name('kepegawaian.pegawai.edit');
            Route::put('pegawai/{pegawai}', [PegawaiController::class, 'update'])->name('kepegawaian.pegawai.update');
            Route::delete('pegawai/{pegawai}', [PegawaiController::class, 'destroy'])->name('kepegawaian.pegawai.destroy');
            Route::post('pegawai/{pegawai}/pribadi', [PegawaiController::class, 'pribadi'])->name('kepegawaian.pegawai.pribadi');

            Route::get('pengguna', [UserController::class, 'index'])->name('kepegawaian.pengguna');
            Route::get('pengguna/options/pegawais', [UserController::class, 'searchPegawais'])->name('kepegawaian.pengguna.options.pegawais');
            Route::get('pengguna/create', [UserController::class, 'create'])->name('kepegawaian.pengguna.create');
            Route::post('pengguna', [UserController::class, 'store'])->name('kepegawaian.pengguna.store');
            Route::get('pengguna/{user}/edit', [UserController::class, 'edit'])->name('kepegawaian.pengguna.edit');
            Route::put('pengguna/{user}', [UserController::class, 'update'])->name('kepegawaian.pengguna.update');
            Route::post('pengguna/{user}/reset-password', [UserController::class, 'resetPassword'])->name('kepegawaian.pengguna.reset-password');
            Route::delete('pengguna/{user}', [UserController::class, 'deactivate'])->name('kepegawaian.pengguna.deactivate');
            Route::post('pengguna/{id}/restore', [UserController::class, 'restore'])->name('kepegawaian.pengguna.restore');
            Route::delete('pengguna/{id}/force-delete', [UserController::class, 'forceDelete'])->name('kepegawaian.pengguna.force-delete');
        });
    });

    Route::post('pengumuman/dismiss-popup', [PengumumanController::class, 'dismissPopup'])->name('pengumuman.dismiss-popup');

    Route::middleware(['role:super-admin|kepegawaian'])->group(function () {
        Route::post('pengumuman/{pengumuman}/toggle-status', [PengumumanController::class, 'toggleStatus'])->name('pengumuman.toggle-status');
        Route::resource('pengumuman', PengumumanController::class);

        Route::prefix('admin')->group(function () {
            Route::prefix('arsip')->group(function () {
                Route::resource('dokumen', DokumenController::class)->parameters(['dokumen' => 'dokumen']);
                Route::resource('gedung', GedungController::class);
                Route::resource('ruang', RuangController::class);
                Route::resource('lemari', LemariController::class);
                Route::resource('rak', RakController::class);
                Route::resource('lokasi-arsip', LokasiArsipController::class);
            });
            Route::prefix('layanan')->group(function () {
                Route::resource('syarat', SyaratController::class);
                Route::resource('layanan', LayananController::class);
            });
        });
    });

    Route::middleware(['role:super-admin'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::prefix('pengguna')->group(function () {
                Route::resource('permission', PermissionController::class);
                Route::resource('role', RoleController::class);
            });
            Route::prefix('referensi')->group(function () {
                Route::resource('agama', AgamaController::class);
                Route::resource('jabatan', JabatanController::class);
                Route::resource('pangkat', PangkatController::class);
                Route::resource('pendidikan', PendidikanController::class);
                Route::resource('program-studi', ProgramStudiController::class);
                Route::resource('unit-kerja', UnitKerjaController::class);
            });
            Route::prefix('forensics')->group(function () {
                Route::get('audit-logs', [AuditLogController::class, 'index'])->name('admin.forensics.audit-logs.index');
                Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('admin.forensics.audit-logs.show');
            });
        });
    });
});
