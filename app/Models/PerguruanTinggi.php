<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerguruanTinggi extends Model
{
    use HasFactory, Auditable;

    public $timestamps = false;

    protected $fillable = [
        'perguruan_tinggi',
    ];

    public function pendidikans()
    {
        return $this->hasMany(Pendidikan::class);
    }
}
