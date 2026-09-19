<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_see_login_action_and_register_is_disabled_on_landing_page()
    {
        $response = $this->get(route('landing'));

        $response->assertOk()
            ->assertDontSee('id="navActionRegister"', false)
            ->assertSee(route('login'))
            ->assertSee('Login')
            ->assertSee('data-section-link')
            ->assertSee('id="sikat"', false)
            ->assertSee('id="faq"', false)
            ->assertSee('id="testimoni"', false)
            ->assertSee('id="kontak"', false)
            ->assertSee('Klik Disini!')
            ->assertSee('hover:no-underline', false);

        $this->assertSame(4, preg_match_all('/data-section-link\s+href="#/m', $response->getContent()));
    }

    public function test_landing_page_renders_four_employee_statistics_charts()
    {
        $response = $this->get(route('landing'));

        $response->assertOk()
            ->assertSee('Grafik Jumlah Pegawai per Jenis Kelamin')
            ->assertSee('Grafik Jumlah Pegawai per Golongan')
            ->assertSee('Grafik Jumlah Pegawai per Tingkat Pendidikan')
            ->assertSee('Grafik Jumlah Pegawai per Eselon Jabatan')
            ->assertSee('id="chartGender"', false)
            ->assertSee('id="chartGolongan"', false)
            ->assertSee('id="chartPendidikan"', false)
            ->assertSee('id="chartEselon"', false);
    }
}
