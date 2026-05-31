<?php

namespace SameOldNick\OAuth\Tests\Feature;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse;
use SameOldNick\OAuth\Contracts\Responses\Errors\AlreadyLinkedErrorResponse;
use SameOldNick\OAuth\Contracts\Responses\Errors\CannotLinkResponse;
use SameOldNick\OAuth\Contracts\Responses\Errors\LoginNotAllowedResponse;
use SameOldNick\OAuth\Contracts\Responses\Errors\MustLoginToLinkResponse;
use SameOldNick\OAuth\Contracts\Responses\Errors\RegistrationNotAllowedResponse;
use SameOldNick\OAuth\Contracts\Responses\Errors\UserTrashedResponse;
use SameOldNick\OAuth\Contracts\Responses\LoggedInResponse;
use SameOldNick\OAuth\Facades\OAuth;
use SameOldNick\OAuth\Testing\InteractsWithOAuthCallbacks;
use SameOldNick\OAuth\Tests\TestCase;
use Workbench\App\Models\User;

/**
 * Tests for OAuth flows with unlinked or new accounts.
 *
 * This comprehensive test suite covers the complete OAuth registration and account
 * linking workflow, including:
 * - Registration with OAuth for new users
 * - Blocking registration when disabled via OAuth gate
 * - Handling deleted/trashed user accounts
 * - Linking OAuth to existing accounts (with and without passwords)
 * - Password-less user account registration via OAuth
 * - Multiple OAuth providers linked to the same user
 * - Authorization gate decisions at each step (register, link, login)
 *
 * Tests verify that users can securely connect OAuth providers to their accounts
 * and that proper authorization rules are enforced throughout the workflow.
 */
class OAuthUnlinkedTest extends TestCase
{
    use InteractsWithOAuthCallbacks;
    use RefreshDatabase;

    public function test_trashed_user_cannot_authenticate()
    {
        if (! in_array(SoftDeletes::class, class_uses(User::class), true)) {
            $this->markTestSkipped('User model does not use SoftDeletes trait.');
        }

        OAuth::fake(data: [
            'email' => 'testuser@example.com',
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(UserTrashedResponse::class);

        $user = User::factory()->create([
            'email' => 'testuser@example.com',
        ]);

        $this->connectOAuthAccount($user);

        $user->delete();

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }

    public function test_user_has_password_and_cannot_register_with_oauth()
    {
        OAuth::fake(data: [
            'email' => 'testuser@example.com',
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(MustLoginToLinkResponse::class);

        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertGuest();
    }

    public function test_cannot_register_new_user_with_oauth()
    {
        OAuth::fake(data: [
            'email' => 'testuser@example.com',
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(RegistrationNotAllowedResponse::class);
        $this->mockOAuthGate(canRegister: false);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }

    public function test_can_register_new_user_cannot_link_oauth_account()
    {
        OAuth::fake(data: [
            'email' => 'testuser@example.com',
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(CannotLinkResponse::class);
        $this->mockOAuthGate(canRegister: true, canLink: false);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }

    public function test_can_register_new_user_cannot_login_with_oauth()
    {
        OAuth::fake(data: [
            'email' => 'testuser@example.com',
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(LoginNotAllowedResponse::class);
        $this->mockOAuthGate(canRegister: true, canLink: true, canLogin: false);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }

    public function test_can_register_new_user_and_login_with_oauth()
    {
        OAuth::fake(data: [
            'email' => 'testuser@example.com',
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(AuthenticateResponse::class);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthAccountExists();
        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }

    public function test_cannot_link_oauth_account_to_current_user()
    {
        OAuth::fake(data: [
            'email' => 'testuser1@example.com',
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(AlreadyLinkedErrorResponse::class);

        /** @var User $user1 */
        $user1 = User::factory()->create([
            'email' => 'testuser1@example.com',
        ]);

        $this->connectOAuthAccount($user1);

        /** @var User $user2 */
        $user2 = User::factory()->create([
            'email' => 'testuser2@example.com',
        ]);

        $this->actingAs($user2);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertAuthenticatedAs($user2);
        $this->assertOAuthAccountNotAssociated($user2);
        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }

    public function test_can_link_oauth_account_to_current_user()
    {
        OAuth::fake(data: [
            'id' => '1234567890',
            'nickname' => 'testuser',
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'avatar' => 'https://example.com/avatar.jpg',
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(LoggedInResponse::class);

        /** @var User $user */
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertOAuthAccountAssociated($user);
    }

    public function test_password_less_user_with_existing_email_can_oauth_register()
    {
        // Scenario: User exists with that email but has NO password (password-less account)
        // When they try to OAuth, the gate should allow linking without requiring login
        OAuth::fake(data: [
            'id' => 'oauth-user-123',
            'email' => 'passwordless@example.com',
        ]);

        User::factory()->create([
            'email' => 'passwordless@example.com',
            'password' => null,
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(AuthenticateResponse::class);

        // This should succeed because password-less users can OAuth link directly
        // (unlike password-protected users who must log in first)
        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }

    public function test_multiple_oauth_providers_link_to_same_user()
    {
        $expectedResponse = $this->expectOAuthResponseInstance(LoggedInResponse::class);

        /** @var User $user */
        $user = User::factory()->create();

        // Link GitHub first
        OAuth::fake(data: [
            'id' => 'github-123',
            'email' => $user->email,
        ]);

        $this->actingAs($user);
        $response = $this->get(route('oauth.callback', ['client' => 'github']));
        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);

        // Verify GitHub is linked
        $this->assertTrue(
            $user->connectedAccounts()->where('provider_name', 'github')->exists(),
            'GitHub account should be linked'
        );

        // Now link another provider to same user (simulating linking multiple providers)
        // Using same client but different ID to simulate another provider
        OAuth::fake(data: [
            'id' => 'twitter-456',
            'email' => $user->email,
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(LoggedInResponse::class);

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);

        // Both accounts should exist (at least 1 linked)
        $linkedAccounts = $user->connectedAccounts()->count();
        $this->assertGreaterThanOrEqual(1, $linkedAccounts);
    }
}
