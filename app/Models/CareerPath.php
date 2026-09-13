<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CareerPath extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'jabatan_asal_id',
        'jabatan_tujuan_id',
        'persyaratan',
        'kompetensi_belum_terpenuhi',
        'pendidikan_belum_terpenuhi',
        'pengalaman_minimal_tahun',
    ];

    protected $casts = [
        'persyaratan' => 'array',
        'kompetensi_belum_terpenuhi' => 'array',
        'pendidikan_belum_terpenuhi' => 'array',
        'pengalaman_minimal_tahun' => 'integer',
    ];

    public function jabatan_asal()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_asal_id');
    }

    public function jabatan_tujuan()
    {
        return $this->belongsTo(Jabatan::class, 'jabatan_tujuan_id');
    }
}
