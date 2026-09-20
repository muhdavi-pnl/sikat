<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudiLanjut extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $table = 'studi_lanjuts';

    public const PROGRES_DEFER = 'defer';
    public const PROGRES_ONGOING = 'ongoing';
    public const PROGRES_SELESAI = 'selesai';

    public const PROGRES_OPTIONS = [
        self::PROGRES_ONGOING => 'Ongoing (Sedang Berjalan)',
        self::PROGRES_DEFER => 'Defer (Ditunda)',
        self::PROGRES_SELESAI => 'Selesai',
    ];

    public const JENIS_PEMBIAYAAN_MANDIRI = 'mandiri';
    public const JENIS_PEMBIAYAAN_BEASISWA = 'beasiswa';

    public const JENIS_PEMBIAYAAN_OPTIONS = [
        self::JENIS_PEMBIAYAAN_BEASISWA => 'Beasiswa',
        self::JENIS_PEMBIAYAAN_MANDIRI => 'Mandiri',
    ];

    public const JENIS_TUGAS_MENINGGALKAN = 'Meninggalkan Tugas';
    public const JENIS_TUGAS_MENJALANKAN = 'Menjalankan Tugas';

    public const JENIS_TUGAS_OPTIONS = [
        self::JENIS_TUGAS_MENINGGALKAN => 'Meninggalkan Tugas',
        self::JENIS_TUGAS_MENJALANKAN => 'Menjalankan Tugas',
    ];

    public const BIDANG_ILMU_STEM = 'STEM';
    public const BIDANG_ILMU_EKONOMI = 'EKONOMI';
    public const BIDANG_ILMU_SOSIAL = 'SOSIAL';
    public const BIDANG_ILMU_HUMANIORA = 'HUMANIORA';
    public const BIDANG_ILMU_KEAGAMAAN = 'KEAGAMAAN';

    public const BIDANG_ILMU_OPTIONS = [
        self::BIDANG_ILMU_STEM => 'STEM',
        self::BIDANG_ILMU_EKONOMI => 'EKONOMI',
        self::BIDANG_ILMU_SOSIAL => 'SOSIAL',
        self::BIDANG_ILMU_HUMANIORA => 'HUMANIORA',
        self::BIDANG_ILMU_KEAGAMAAN => 'KEAGAMAAN',
    ];

    public const JENJANG_OPTIONS = [
        'S2' => 'Magister (S2)',
        'S3' => 'Doktor (S3)',
        'Spesialis' => 'Spesialis (Sp-1 / Sp-2)',
        'Subspesialis' => 'Subspesialis',
        'Postdoctoral' => 'Postdoctoral',
    ];

    protected $fillable = [
        'pegawai_id',
        'progres',
        'jenis_pembiayaan',
        'nama_beasiswa',
        'jenis_tugas',
        'bidang_ilmu',
        'jenjang',
        'program_studi',
        'nama_institusi',
        'negara',
        'tanggal_mulai',
        'target_selesai',
        'tanggal_selesai',
        'nomor_sk',
        'tanggal_sk',
        'dokumen_sk',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'target_selesai' => 'date',
        'tanggal_selesai' => 'date',
        'tanggal_sk' => 'date',
    ];

    protected $appends = [
        'progres_label',
        'progres_badge_class',
        'jenis_pembiayaan_label',
    ];

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'pegawai_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProgresLabelAttribute(): string
    {
        return self::PROGRES_OPTIONS[$this->progres] ?? ucfirst($this->progres ?? '');
    }

    public function getProgresBadgeClassAttribute(): string
    {
        return match ($this->progres) {
            self::PROGRES_ONGOING => 'badge-info',
            self::PROGRES_DEFER => 'badge-warning',
            self::PROGRES_SELESAI => 'badge-success',
            default => 'badge-secondary',
        };
    }

    public function getJenisPembiayaanLabelAttribute(): string
    {
        return self::JENIS_PEMBIAYAAN_OPTIONS[$this->jenis_pembiayaan] ?? ucfirst($this->jenis_pembiayaan ?? '');
    }

    public function getBidangIlmuBadgeClassAttribute(): string
    {
        return match ($this->bidang_ilmu) {
            self::BIDANG_ILMU_STEM => 'badge-primary',
            self::BIDANG_ILMU_EKONOMI => 'badge-success',
            self::BIDANG_ILMU_SOSIAL => 'badge-info',
            self::BIDANG_ILMU_HUMANIORA => 'badge-warning',
            self::BIDANG_ILMU_KEAGAMAAN => 'badge-dark',
            default => 'badge-light',
        };
    }
}
