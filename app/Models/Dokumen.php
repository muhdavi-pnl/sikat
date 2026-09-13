<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'kode_dokumen',
        'nama_dokumen',
    ];

    public $timestamps = false;

    public function pegawai()
    {
        return $this->belongsToMany(Pegawai::class)->withPivot(['user_id', 'file', 'nomor', 'tanggal', 'status', 'keterangan']);
    }

    public function dokumenPegawais()
    {
        return $this->hasMany(DokumenPegawai::class);
    }
}
