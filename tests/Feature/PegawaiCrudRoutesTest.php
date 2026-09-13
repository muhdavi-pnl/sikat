<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PegawaiCrudRoutesTest extends TestCase
{
    /** @test */
    public function kepegawaian_pegawai_crud_routes_are_registered()
    {
        $this->assertTrue(Route::has('kepegawaian.pegawai'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.create'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.store'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.show'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.edit'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.update'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.destroy'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.export'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.print'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.options.users'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.options.jabatans'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.options.program-studis'));
        $this->assertTrue(Route::has('kepegawaian.pegawai.options.kelurahans'));
        $this->assertTrue(Route::has('pegawai.wilayah.provinsis'));
        $this->assertTrue(Route::has('pegawai.wilayah.kabupatens'));
        $this->assertTrue(Route::has('pegawai.wilayah.kecamatans'));
        $this->assertTrue(Route::has('pegawai.wilayah.kelurahans'));
    }
}
