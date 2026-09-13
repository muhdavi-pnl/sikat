<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jabatan extends Model
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $fillable = [
        'kode_jabatan',
        'jabatan',
        'unit_kerja_id',
        'jenis_jabatan_id',
        'status_jabatan',
        'jenjang_jabatan',
        'kelas_jabatan',
        'pangkat_golongan',
        'pendidikan_minimal',
        'kompetensi',
        'ikhtisar_jabatan',
        'uraian_tugas',
        'tanggung_jawab',
        'wewenang',
        'persyaratan_jabatan',
        'beban_kerja',
        'atasan_langsung_id',
        'kebutuhan_pegawai',
    ];

    public function jenis_jabatan()
    {
        return $this->belongsTo(JenisJabatan::class);
    }

    public function unit_kerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function atasan_langsung()
    {
        return $this->belongsTo(self::class, 'atasan_langsung_id');
    }

    public function bawahan()
    {
        return $this->hasMany(self::class, 'atasan_langsung_id');
    }

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function careerPaths()
    {
        return $this->hasMany(CareerPath::class, 'jabatan_asal_id');
    }
}
