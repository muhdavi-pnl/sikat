<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Jabatan extends Model
{
    use HasFactory, SoftDeletes, Auditable;

    protected $fillable = [
        'kode_jabatan',
        'jabatan',
        'jenis_jabatan_id',
        'kelas_jabatan',
        'pangkat_minimal',
        'pangkat_golongan',
        'pendidikan_minimal',
        'kompetensi',
        'ikhtisar_jabatan',
        'uraian_tugas',
        'tanggung_jawab',
        'wewenang',
        'persyaratan_jabatan',
        'beban_kerja',
    ];

    public function jenis_jabatan()
    {
        return $this->belongsTo(JenisJabatan::class, 'jenis_jabatan_id');
    }

    public function pangkat_minimal_rel()
    {
        return $this->belongsTo(Pangkat::class, 'pangkat_minimal');
    }

    public function pangkatMinimal()
    {
        return $this->belongsTo(Pangkat::class, 'pangkat_minimal');
    }

    public function peta_jabatan()
    {
        return $this->hasOne(PetaJabatan::class);
    }

    public function unit_kerja()
    {
        return $this->hasOneThrough(UnitKerja::class, PetaJabatan::class, 'jabatan_id', 'id', 'id', 'unit_kerja_id');
    }

    public function atasan_langsung()
    {
        return $this->hasOneThrough(Jabatan::class, PetaJabatan::class, 'jabatan_id', 'id', 'id', 'atasan_langsung_id');
    }

    public function bawahan()
    {
        return $this->hasManyThrough(Jabatan::class, PetaJabatan::class, 'atasan_langsung_id', 'id', 'id', 'jabatan_id');
    }

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class, 'jabatan_id');
    }

    public function pegawais_rangkap()
    {
        return $this->hasMany(Pegawai::class, 'jabatan_rangkap_id');
    }

    public function pegawais_struktural()
    {
        return $this->pegawais_rangkap();
    }

    public function careerPaths()
    {
        return $this->hasMany(CareerPath::class, 'jabatan_asal_id');
    }

    public function getKebutuhanPegawaiAttribute(): int
    {
        if (array_key_exists('kebutuhan_pegawai', $this->attributes)) {
            return (int) $this->attributes['kebutuhan_pegawai'];
        }

        return (int) ($this->peta_jabatan?->kebutuhan_pegawai ?? 0);
    }

    public function getUnitKerjaIdAttribute(): ?int
    {
        if (array_key_exists('unit_kerja_id', $this->attributes)) {
            return $this->attributes['unit_kerja_id'];
        }

        return $this->peta_jabatan?->unit_kerja_id;
    }

    public function getAtasanLangsungIdAttribute(): ?int
    {
        if (array_key_exists('atasan_langsung_id', $this->attributes)) {
            return $this->attributes['atasan_langsung_id'];
        }

        return $this->peta_jabatan?->atasan_langsung_id;
    }
}
