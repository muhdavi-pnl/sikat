<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_shows_google_auth_link()
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee(route('auth.google.redirect'))
            ->assertSee('Login dengan Google');
    }

    public function test_users_are_redirected_to_google_for_authentication()
    {
        config(['services.google.redirect' => '']);

        $provider = Mockery::mock();

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $provider->shouldReceive('redirectUrl')
            ->once()
            ->with(route('auth.google.callback'))
            ->andReturnSelf();

        $provider->shouldReceive('redirect')
            ->once()
            ->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));

        $this->get(route('auth.google.redirect'))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    public function test_new_users_can_register_using_google_account()
    {
        $this->mockGoogleUserResponse($this->makeGoogleUser([
            'id' => 'google-user-1',
            'name' => 'Google Test User',
            'email' => 'google-user@example.com',
            'email_verified' => true,
        ]));

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);

        $this->assertDatabaseHas('users', [
            'email' => 'google-user@example.com',
            'name' => 'Google Test User',
            'google_id' => 'google-user-1',
        ]);

        $user = User::where('email', 'google-user@example.com')->firstOrFail();
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotEmpty($user->password);
    }

    public function test_existing_users_are_linked_and_logged_in_using_google_account()
    {
        $existingUser = User::factory()->create([
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'google_id' => null,
            'email_verified_at' => null,
        ]);

        $this->mockGoogleUserResponse($this->makeGoogleUser([
            'id' => 'google-user-2',
            'name' => 'Existing User',
            'email' => 'existing@example.com',
            'email_verified' => true,
        ]));

        $response = $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($existingUser->fresh());
        $response->assertRedirect(RouteServiceProvider::HOME);

        $existingUser->refresh();
        $this->assertSame('google-user-2', $existingUser->google_id);
        $this->assertNotNull($existingUser->email_verified_at);
    }

    public function test_google_auth_requires_verified_email_address()
    {
        $this->mockGoogleUserResponse($this->makeGoogleUser([
            'id' => 'google-user-3',
            'name' => 'No Email User',
            'email' => '',
            'email_verified' => false,
        ]));

        $response = $this->from('/login')->get(route('auth.google.callback'));

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
        $this->assertDatabaseCount('users', 0);
    }

    protected function makeGoogleUser(array $attributes): SocialiteUser
    {
        $user = new SocialiteUser();

        $raw = [
            'sub' => $attributes['id'],
            'name' => $attributes['name'] ?? null,
            'email' => $attributes['email'] ?? null,
            'email_verified' => $attributes['email_verified'] ?? false,
            'verified_email' => $attributes['email_verified'] ?? false,
        ];

        $user->setRaw($raw)->map([
            'id' => $attributes['id'],
            'nickname' => null,
            'name' => $attributes['name'] ?? null,
            'email' => $attributes['email'] ?? null,
            'avatar' => null,
        ]);

        return $user;
    }

    protected function mockGoogleUserResponse(SocialiteUser $socialiteUser): void
    {
        config(['services.google.redirect' => '']);

        $provider = Mockery::mock();

        Socialite::shouldReceive('driver')
            ->once()
            ->with('google')
            ->andReturn($provider);

        $provider->shouldReceive('redirectUrl')
            ->once()
            ->with(route('auth.google.callback'))
            ->andReturnSelf();

        $provider->shouldReceive('user')
            ->once()
            ->andReturn($socialiteUser);
    }
}

