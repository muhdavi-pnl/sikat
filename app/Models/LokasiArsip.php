<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LokasiArsip extends Model
{
    use HasFactory;

    protected $fillable = [
        'rak_id',
        'pegawai_id',
        'keterangan',
    ];

    public function rak()
    {
        return $this->belongsTo(Rak::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class);
    }
}
