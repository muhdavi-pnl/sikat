<?php

namespace Tests\Feature;

use Tests\TestCase;

class LandingPageTest extends TestCase
{
    public function test_guest_can_see_register_and_login_actions_on_landing_page()
    {
        $response = $this->get(route('landing'));

        $response->assertOk()
            ->assertSee(route('register'))
            ->assertSee('Register')
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
}

