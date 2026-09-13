<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rak extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'lemari_id',
        'rak',
        'keterangan',
    ];

    public function lemari()
    {
        return $this->belongsTo(Lemari::class);
    }

    public function lokasiArsips()
    {
        return $this->hasMany(LokasiArsip::class);
    }
}
