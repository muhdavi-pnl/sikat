<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisJabatan extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'jenis_jabatan',
    ];

    public function getKodeAttribute(): string
    {
        $name = strtolower((string) $this->jenis_jabatan);
        if (str_contains($name, 'pelaksana') || str_contains($name, 'fungsional umum')) {
            return 'JP';
        }
        if (str_contains($name, 'fungsional')) {
            return 'JF';
        }
        if (str_contains($name, 'struktural')) {
            return 'JS';
        }
        if (str_contains($name, 'rangkap')) {
            return 'JR';
        }

        return 'JB';
    }
}
