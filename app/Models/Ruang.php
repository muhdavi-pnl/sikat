<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'gedung_id',
        'kode_ruang',
        'nama_ruang',
        'keterangan',
    ];

    public function gedung()
    {
        return $this->belongsTo(Gedung::class);
    }

    public function lemaris()
    {
        return $this->hasMany(Lemari::class);
    }
}
