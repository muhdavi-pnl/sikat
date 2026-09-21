<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PegawaiCutiQuota extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'pegawai_id',
        'tahun',
        'hari_tersedia',
        'keterangan',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'hari_tersedia' => 'integer',
    ];

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
