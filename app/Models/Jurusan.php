<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory;

    public function perguruan_tinggi()
    {
        return $this->belongsTo(PerguruanTinggi::class);
    }
}
