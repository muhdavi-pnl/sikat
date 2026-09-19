<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $fillable = [
        'jurusan',
    ];

    public function program_studis()
    {
        return $this->hasMany(ProgramStudi::class);
    }

    public function kelompok_keahlians()
    {
        return $this->hasMany(KelompokKeahlian::class);
    }
}
