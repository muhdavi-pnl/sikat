<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Syarat extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    public const MAPPING_SOURCE_MANUAL = 'manual';
    public const MAPPING_SOURCE_DOCUMENT = 'document';
    public const MAPPING_SOURCE_PROFILE = 'profile';
    public const MAPPING_SOURCE_GENERATED = 'generated';

    protected $fillable = [
        'kode_syarat',
        'syarat',
        'dokumen_id',
    ];

    public function dokumen()
    {
        return $this->belongsTo(Dokumen::class);
    }

    public function layanan()
    {
        return $this->belongsToMany(Layanan::class);
    }

    public function mappingSource(): string
    {
        if ($this->isSystemGenerated()) {
            return self::MAPPING_SOURCE_GENERATED;
        }

        if ($this->dokumen_id) {
            return self::MAPPING_SOURCE_DOCUMENT;
        }

        if ($this->kode_syarat) {
            return self::MAPPING_SOURCE_PROFILE;
        }

        return self::MAPPING_SOURCE_MANUAL;
    }

    public function mappingLabel(): string
    {
        return [
            self::MAPPING_SOURCE_GENERATED => 'Formulir Sistem',
            self::MAPPING_SOURCE_DOCUMENT => 'Dokumen Pegawai',
            self::MAPPING_SOURCE_PROFILE => 'Data Profil Pegawai',
            self::MAPPING_SOURCE_MANUAL => 'Verifikasi Manual',
        ][$this->mappingSource()];
    }

    public function mappingBadgeClass(): string
    {
        return [
            self::MAPPING_SOURCE_GENERATED => 'badge-warning',
            self::MAPPING_SOURCE_DOCUMENT => 'badge-primary',
            self::MAPPING_SOURCE_PROFILE => 'badge-info',
            self::MAPPING_SOURCE_MANUAL => 'badge-secondary',
        ][$this->mappingSource()];
    }

    public function isSystemGenerated(): bool
    {
        return mb_strtoupper(trim((string) $this->kode_syarat)) === 'CUTI_FORM';
    }
}
