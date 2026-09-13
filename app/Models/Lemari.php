<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lemari extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'ruang_id',
        'lemari',
        'keterangan',
    ];

    public function ruang()
    {
        return $this->belongsTo(Ruang::class);
    }

    public function raks()
    {
        return $this->hasMany(Rak::class);
    }
}
