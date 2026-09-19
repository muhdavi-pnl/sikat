<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendidikan extends Model
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $fillable = [
        'pendidikan',
        'perguruan_tinggi_id',
        'tingkat_pendidikan_id',
    ];

    public function pegawais()
    {
        return $this->hasMany(Pegawai::class);
    }

    public function perguruan_tinggi()
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }

    public function tingkat_pendidikan()
    {
        return $this->belongsTo(TingkatPendidikan::class);
    }
}
