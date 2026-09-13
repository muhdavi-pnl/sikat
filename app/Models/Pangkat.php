<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pangkat extends Model
{
    use HasFactory, Auditable;

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }
}
