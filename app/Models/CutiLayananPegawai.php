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

    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_PERUBAHAN = 'perubahan';
    public const STATUS_DITANGGUHKAN = 'ditangguhkan';
    public const STATUS_TIDAK_DISETUJUI = 'tidak_disetujui';

    protected $fillable = [
        'layanan_pegawai_id',
        'jenis_cuti',
        'alasan_cuti',
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
        'atasan_approved_at' => 'datetime',
        'pybmc_approved_at' => 'datetime',
    ];

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

