<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendidikan extends Model
{
    use HasFactory, Auditable;

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function tingkat_pendidikan()
    {
        return $this->belongsTo(TingkatPendidikan::class);
    }
}
