<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CutiLayananPegawai extends Model
{
    use HasFactory, Auditable;

    public const STAGE_ATASAN = 'atasan';
    public const STAGE_PYBMC = 'pybmc';
    public const STAGE_SELESAI = 'selesai';
    public const STAGE_DITOLAK = 'ditolak';
    public const STAGE_DIBATALKAN = 'dibatalkan';

    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_PERUBAHAN = 'perubahan';
    public const STATUS_DITANGGUHKAN = 'ditangguhkan';
    public const STATUS_TIDAK_DISETUJUI = 'tidak_disetujui';

    // Alasan Cuti Karena Alasan Penting (CAP - Khusus PNS)
    public const ALASAN_CAP_KELUARGA_SAKIT_MENINGGAL = 'IBU_BAPAK_ISTRI_SUAMI_ANAK_SAKIT_KERAS_MENINGGAL';
    public const ALASAN_CAP_PERKAWINAN_PERTAMA = 'PERKAWINAN_PERTAMA';
    public const ALASAN_CAP_BENCANA_ALAM = 'BENCANA_ALAM';
    public const ALASAN_CAP_ISTRI_MELAHIRKAN_OPERASI = 'ISTRI_MELAHIRKAN_OPERASI';

    // Alasan Bypass Cuti Tahunan PPPK (< 1 Tahun)
    public const ALASAN_PPPK_PERKAWINAN_PERTAMA = 'PERKAWINAN_PERTAMA';
    public const ALASAN_PPPK_KELUARGA_INTI_SAKIT_KERAS_MENINGGAL = 'KELUARGA_INTI_SAKIT_KERAS_MENINGGAL';

    // Kategori Khusus Cuti Sakit
    public const KATEGORI_SAKIT_REGULER = 'reguler';
    public const KATEGORI_SAKIT_GUGUR_KANDUNGAN = 'gugur_kandungan';
    public const KATEGORI_SAKIT_KECELAKAAN_KERJA = 'kecelakaan_kerja';

    protected $fillable = [
        'layanan_pegawai_id',
        'jenis_cuti',
        'kategori_cuti',
        'alasan_cuti',
        'alasan_cap',
        'alasan_pppk_bypass',
        'kelahiran_anak_ke',
        'rekomendasi_tim_penguji_kesehatan',
        'alamat_menjalankan_cuti',
        'nomor_telepon_cuti',
        'tanggal_mulai',
        'tanggal_selesai',
        'hari_diminta',
        'hari_tersedia_saat_usul',
        'atasan_pegawai_id',
        'atasan_user_id',
        'atasan_status',
        'catatan_atasan',
        'atasan_approved_at',
        'pybmc_pegawai_id',
        'pybmc_user_id',
        'pybmc_status',
        'catatan_pybmc',
        'pybmc_approved_at',
        'approval_stage',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'hari_diminta' => 'integer',
        'hari_tersedia_saat_usul' => 'integer',
        'kelahiran_anak_ke' => 'integer',
        'rekomendasi_tim_penguji_kesehatan' => 'boolean',
        'atasan_approved_at' => 'datetime',
        'pybmc_approved_at' => 'datetime',
    ];

    public static function alasanCapOptions(): array
    {
        return [
            self::ALASAN_CAP_KELUARGA_SAKIT_MENINGGAL => 'Keluarga Inti (Ibu/Bapak/Istri/Suami/Anak/Adik/Kakak/Mertua) Sakit Keras atau Meninggal Dunia',
            self::ALASAN_CAP_PERKAWINAN_PERTAMA => 'Melangsungkan Perkawinan Pertama',
            self::ALASAN_CAP_BENCANA_ALAM => 'Mengalami Bencana Alam / Kebakaran',
            self::ALASAN_CAP_ISTRI_MELAHIRKAN_OPERASI => 'Mendampingi Istri Melahirkan / Operasi Sesar',
        ];
    }

    public static function alasanPppkBypassOptions(): array
    {
        return [
            self::ALASAN_PPPK_PERKAWINAN_PERTAMA => 'Perkawinan Pertama (Maksimal 6 hari kerja)',
            self::ALASAN_PPPK_KELUARGA_INTI_SAKIT_KERAS_MENINGGAL => 'Keluarga Inti Sakit Keras / Meninggal Dunia (Maksimal 6 hari kerja)',
        ];
    }

    public static function kategoriSakitOptions(): array
    {
        return [
            self::KATEGORI_SAKIT_REGULER => 'Sakit Biasa / Rawat Inap / Rawat Jalan',
            self::KATEGORI_SAKIT_GUGUR_KANDUNGAN => 'Gugur Kandungan (Maksimal 45 hari kalender)',
            self::KATEGORI_SAKIT_KECELAKAAN_KERJA => 'Kecelakaan Kerja (Dalam / Karena Menjalankan Tugas)',
        ];
    }

    public function layananPegawai()
    {
        return $this->belongsTo(LayananPegawai::class, 'layanan_pegawai_id');
    }

    public function atasanPegawai()
    {
        return $this->belongsTo(Pegawai::class, 'atasan_pegawai_id');
    }

    public function atasanUser()
    {
        return $this->belongsTo(User::class, 'atasan_user_id');
    }

    public function pybmcPegawai()
    {
        return $this->belongsTo(Pegawai::class, 'pybmc_pegawai_id');
    }

    public function pybmcUser()
    {
        return $this->belongsTo(User::class, 'pybmc_user_id');
    }

    public function isApprovedByAtasan(): bool
    {
        return $this->atasan_status === self::STATUS_DISETUJUI;
    }

    public function isApprovedByPybmc(): bool
    {
        return $this->pybmc_status === self::STATUS_DISETUJUI;
    }

    public static function approvalStatusOptions(): array
    {
        return [
            self::STATUS_DISETUJUI => 'Disetujui',
            self::STATUS_PERUBAHAN => 'Perubahan',
            self::STATUS_DITANGGUHKAN => 'Ditangguhkan',
            self::STATUS_TIDAK_DISETUJUI => 'Tidak Disetujui',
        ];
    }
}

