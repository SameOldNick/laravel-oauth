<?php

namespace SameOldNick\OAuth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Mockery;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse;
use SameOldNick\OAuth\Enums\OAuthError;
use SameOldNick\OAuth\Facades\OAuth;
use SameOldNick\OAuth\Testing\InteractsWithOAuthCallbacks;
use SameOldNick\OAuth\Tests\TestCase;
use Workbench\App\Models\User;

/**
 * Tests for OAuth flow edge cases and error handling.
 *
 * This test suite verifies OAuth behavior in unusual or error conditions, including:
 * - Unknown or unconfigured OAuth clients
 * - Invalid OAuth state and provider connection errors
 * - Two-factor authentication requirements for linked users
 * - New user registration via OAuth
 * - Email conflicts with existing password-protected accounts
 * - Provider connection failures and bad request handling
 *
 * These tests ensure the OAuth system gracefully handles errors and maintains
 * security requirements in edge cases.
 */
class OAuthFlowEdgeCasesTest extends TestCase
{
    use InteractsWithOAuthCallbacks;
    use RefreshDatabase;

    public function test_unknown_client_returns_not_found_on_redirect(): void
    {
        $this->get(route('oauth.redirect', ['client' => 'unsupported-provider']))
            ->assertNotFound();
    }

    public function test_unknown_client_returns_not_found_on_callback(): void
    {
        $this->get(route('oauth.callback', ['client' => 'unsupported-provider']))
            ->assertNotFound();
    }

    public function test_unconfigured_client_returns_not_found_on_callback(): void
    {
        config(['oauth.clients.github.enabled' => false]);

        $this->get(route('oauth.callback', ['client' => 'github']))
            ->assertNotFound();
    }

    public function test_callback_returns_bad_request_when_oauth_state_is_invalid(): void
    {
        config([
            'oauth.clients.github.enabled' => true,
            'oauth.clients.github.client_id' => 'test-client-id',
            'oauth.clients.github.client_secret' => 'test-client-secret',
        ]);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andThrow(new InvalidStateException('Bad state.'));

        Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

        $this->get(route('oauth.callback', ['client' => 'github']))
            ->assertStatus(400)
            ->assertSeeText("An OAuth response was received that wasn't expected.");
    }

    public function test_linked_user_with_two_factor_redirects_to_two_factor_challenge(): void
    {
        if (! in_array(TwoFactorAuthenticatable::class, class_uses(User::class), true)) {
            $this->markTestSkipped('User model does not use TwoFactorAuthenticatable trait.');
        }

        OAuth::fake();

        $user = User::factory()->create([
            'two_factor_secret' => 'oauth-two-factor-secret',
            'two_factor_confirmed_at' => now(),
        ]);

        $this->connectOAuthAccount($user);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $response->assertRedirect('/two-factor-challenge');

        $this->assertGuest();
        $this->assertSame((string) $user->getKey(), (string) session('login.id'));
        $this->assertFalse((bool) session('login.remember'));
    }

    public function test_newly_registered_user_with_oauth_is_authenticated(): void
    {
        OAuth::fake(data: [
            'email' => 'newuser@example.com',
        ]);

        config([
            'oauth.allow_registration' => true,
            'oauth.require_verified_email_to_link' => false,
        ]);

        $this->assertOAuthResponseReturned(AuthenticateResponse::class);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        // New user should be authenticated
        $response->assertRedirect();
        $this->assertAuthenticated();
        $this->assertSame('newuser@example.com', auth()->user()?->email);
    }

    public function test_provider_connection_error_returns_bad_request(): void
    {
        config([
            'oauth.clients.github.enabled' => true,
            'oauth.clients.github.client_id' => 'test-client-id',
            'oauth.clients.github.client_secret' => 'test-client-secret',
        ]);

        $exception = new \Exception('Provider connection failed');

        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andThrow($exception);

        Socialite::shouldReceive('driver')->once()->with('github')->andReturn($provider);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        // Should return 400 Bad Request with error message
        $response->assertStatus(400);
    }

    public function test_email_exists_with_password_requires_login_before_oauth()
    {
        OAuth::fake(data: [
            'email' => 'existing@example.com',
        ]);

        $this->assertOAuthResponseReturned($this->mockErrorResponse(OAuthError::MustLoginToLink));

        // User with password already exists
        User::factory()->create([
            'email' => 'existing@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        // Should require login first
        $this->assertGuest();
    }
}
