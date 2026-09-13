<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitKerja extends Model
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $fillable = [
        'unit_kerja',
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }
}
