<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gedung extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'nama_gedung',
        'alamat_gedung',
        'keterangan',
    ];

    public function ruangs()
    {
        return $this->hasMany(Ruang::class);
    }
}
