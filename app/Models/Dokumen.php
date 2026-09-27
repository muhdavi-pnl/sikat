<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    use HasFactory, Auditable;

    const KATEGORI_SEMUA = 'semua';
    const KATEGORI_PNS = 'pns';
    const KATEGORI_PPPK = 'pppk';

    public static function kategoriOptions(): array
    {
        return [
            self::KATEGORI_SEMUA => 'Semua Pegawai (PNS & PPPK)',
            self::KATEGORI_PNS => 'Khusus PNS / CPNS',
            self::KATEGORI_PPPK => 'Khusus PPPK / PPPK Paruh Waktu',
        ];
    }

    protected $fillable = [
        'kode_dokumen',
        'nama_dokumen',
        'kategori_pegawai',
    ];

    protected $attributes = [
        'kategori_pegawai' => 'semua',
    ];

    public $timestamps = false;

    public function getKategoriPegawaiLabelAttribute(): string
    {
        return match ($this->kategori_pegawai) {
            self::KATEGORI_PNS => 'Khusus PNS / CPNS',
            self::KATEGORI_PPPK => 'Khusus PPPK',
            default => 'Semua Pegawai',
        };
    }

    public function getKategoriPegawaiBadgeAttribute(): string
    {
        return match ($this->kategori_pegawai) {
            self::KATEGORI_PNS => 'badge-success',
            self::KATEGORI_PPPK => 'badge-info',
            default => 'badge-primary',
        };
    }

    public function scopeForPegawai($query, ?Pegawai $pegawai)
    {
        if (!$pegawai) {
            return $query;
        }

        if ($pegawai->isPns()) {
            return $query->whereIn('kategori_pegawai', [self::KATEGORI_SEMUA, self::KATEGORI_PNS]);
        }

        if ($pegawai->isPppk()) {
            return $query->whereIn('kategori_pegawai', [self::KATEGORI_SEMUA, self::KATEGORI_PPPK]);
        }

        return $query;
    }

    public function pegawai()
    {
        return $this->belongsToMany(Pegawai::class)->withPivot(['user_id', 'file', 'nomor', 'tanggal', 'status', 'keterangan']);
    }

    public function dokumenPegawais()
    {
        return $this->hasMany(DokumenPegawai::class);
    }
}
