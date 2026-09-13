<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'layanan',
        'deskripsi',
        'jenis',
    ];

    public function syarat()
    {
        return $this->belongsToMany(Syarat::class);
    }

    public function layananPegawais()
    {
        return $this->hasMany(LayananPegawai::class);
    }
}
