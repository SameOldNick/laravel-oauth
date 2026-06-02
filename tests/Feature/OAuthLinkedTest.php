<?php

namespace SameOldNick\OAuth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse;
use SameOldNick\OAuth\Contracts\Responses\ErrorResponse;
use SameOldNick\OAuth\Contracts\Responses\LoggedInResponse;
use SameOldNick\OAuth\Enums\OAuthError;
use SameOldNick\OAuth\Facades\OAuth;
use SameOldNick\OAuth\Testing\InteractsWithOAuthCallbacks;
use SameOldNick\OAuth\Testing\TestOAuthResponse;
use SameOldNick\OAuth\Tests\TestCase;
use Workbench\App\Models\User;

/**
 * Tests for OAuth authentication flows with already-linked accounts.
 *
 * This test suite verifies OAuth behavior when users have previously linked their
 * OAuth account. Tests cover scenarios including:
 * - Re-linking the same OAuth account to the same user
 * - Attempting to re-link when the gate disallows it
 * - Attempting to link an account already associated with another user
 * - Logging in with a previously linked OAuth account
 *
 * These tests ensure proper account linking state management and prevent
 * unauthorized account takeovers or linking violations.
 */
class OAuthLinkedTest extends TestCase
{
    use InteractsWithOAuthCallbacks;
    use RefreshDatabase;

    public function test_relinks_oauth_account_to_current_user()
    {
        OAuth::fake();

        $expectedResponse = $this->expectOAuthResponseInstance(LoggedInResponse::class);

        $user = User::factory()->create();

        $this->connectOAuthAccount($user);

        /** @var User $user */
        $this->actingAs($user);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertAuthenticatedAs($user);

    }

    public function test_cannot_relink_oauth_account_to_current_user()
    {
        OAuth::fake();

        $expectedResponse = $this->expectOAuthResponseInstance(
            TestOAuthResponse::forOAuthErrorResponse(OAuthError::CannotLink),
            abstract: ErrorResponse::class
        );

        $this->mockOAuthGate(canLink: false);

        $user = User::factory()->create();

        $this->connectOAuthAccount($user);

        /** @var User $user */
        $this->actingAs($user);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertAuthenticatedAs($user);
    }

    public function test_relinks_oauth_account_to_other_user()
    {
        OAuth::fake();

        $expectedResponse = $this->expectOAuthResponseInstance(
            TestOAuthResponse::forOAuthErrorResponse(OAuthError::AlreadyLinked),
            abstract: ErrorResponse::class
        );

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->connectOAuthAccount($user1);

        /** @var User $user2 */
        $this->actingAs($user2);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertAuthenticatedAs($user2);
    }

    public function test_logins_with_linked_oauth_account(): void
    {
        OAuth::fake();

        $expectedResponse = $this->expectOAuthResponseInstance(AuthenticateResponse::class);

        $user = User::factory()->create();

        $this->connectOAuthAccount($user);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }
}
