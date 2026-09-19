<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelompokKeahlian extends Model
{
    use HasFactory, Auditable;

    protected $table = 'kelompok_keahlians';

    protected $fillable = [
        'nama_kelompok',
        'deskripsi',
        'ketua_kelompok',
        'bidang_penelitian',
        'jurusan_id',
    ];

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    public function pegawaiIdentitas()
    {
        return $this->hasMany(PegawaiIdentitas::class, 'kelompok_keahlian_id');
    }
}
