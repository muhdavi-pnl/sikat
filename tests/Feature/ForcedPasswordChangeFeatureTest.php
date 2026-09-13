<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcedPasswordChangeFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function flagged_user_is_redirected_to_force_change_page_from_dashboard()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Sikat2019'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('password.force.edit'));

        $this->actingAs($user)
            ->get(route('password.force.edit'))
            ->assertOk();
    }

    /** @test */
    public function flagged_user_can_change_password_and_access_dashboard()
    {
        $user = User::factory()->create([
            'password' => Hash::make('Sikat2019'),
            'must_change_password' => true,
        ]);

        $this->actingAs($user)
            ->put(route('password.force.update'), [
                'current_password' => 'Sikat2019',
                'password' => 'NewSecurePass123',
                'password_confirmation' => 'NewSecurePass123',
            ])
            ->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertFalse((bool) $user->must_change_password);
        $this->assertTrue(Hash::check('NewSecurePass123', $user->password));

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }
}

