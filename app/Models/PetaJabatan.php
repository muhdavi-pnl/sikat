<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetaJabatan extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'jabatan_id',
        'unit_kerja_id',
        'atasan_langsung_id',
        'kebutuhan_pegawai',
    ];

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function unit_kerja()
    {
        return $this->belongsTo(UnitKerja::class);
    }

    public function atasan_langsung()
    {
        return $this->belongsTo(Jabatan::class, 'atasan_langsung_id');
    }
}
