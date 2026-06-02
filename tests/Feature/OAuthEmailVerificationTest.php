<?php

namespace SameOldNick\OAuth\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse;
use SameOldNick\OAuth\Contracts\Responses\ErrorResponse;
use SameOldNick\OAuth\Enums\OAuthError;
use SameOldNick\OAuth\Facades\OAuth;
use SameOldNick\OAuth\Testing\InteractsWithOAuthCallbacks;
use SameOldNick\OAuth\Testing\TestOAuthResponse;
use SameOldNick\OAuth\Tests\TestCase;
use Workbench\App\Models\User;

/**
 * Tests for OAuth flows involving email verification requirements.
 *
 * This suite verifies the interplay between oauth.allow_registration,
 * oauth.email_verification_required config flags, and provider-level email
 * verification signals across all three OAuth path variants:
 *
 * - New user registration (no existing account, no linked account)
 * - Existing unlinked user (account exists, no OAuth link yet)
 * - Existing linked user (account exists with an OAuth link)
 *
 * Tests confirm that:
 * - Registration can be blocked at the gate level via config
 * - Email verification is enforced at the canLink step for unverified users
 * - Google's email_verified signal triggers markEmailAsVerified for new users
 * - Already-linked users can log in regardless of their email verification status
 *   (email verification is only a linking concern, not a login concern)
 */
class OAuthEmailVerificationTest extends TestCase
{
    use InteractsWithOAuthCallbacks;
    use RefreshDatabase;

    /**
     * Extend the base trait setup to also configure the Google OAuth client,
     * which is required by the Google-specific test scenarios.
     */
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'oauth.clients.google.enabled' => true,
            'oauth.clients.google.client_id' => 'test-google-client-id',
            'oauth.clients.google.client_secret' => 'test-google-client-secret',
        ]);
    }

    /**
     * Scenario 1: Registration is disabled. Email verification not required.
     * User does not exist. User signs in via OAuth.
     *
     * Expected: The real gate's canRegister() returns false → RegistrationNotAllowed error.
     * This tests the config-level registration gate without mocking.
     */
    public function test_registration_disabled_new_user_cannot_authenticate_via_oauth(): void
    {
        config(['oauth.allow_registration' => false]);

        OAuth::fake(data: ['email' => 'newuser@example.com']);

        $expectedResponse = $this->expectOAuthResponseInstance(
            TestOAuthResponse::forOAuthErrorResponse(OAuthError::RegistrationNotAllowed),
            abstract: ErrorResponse::class
        );

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertGuest();
    }

    /**
     * Scenario 2: Registration is enabled. Email verification is required.
     * User does not exist. Provider is GitHub, which does not indicate email verification.
     *
     * Expected: A new user is registered but their email remains unverified. The
     * canLink gate then blocks linking because the user has not verified their email →
     * EmailVerificationRequired error.
     */
    public function test_registration_with_unverified_email_provider_blocks_at_link_step(): void
    {
        config(['oauth.email_verification_required' => true]);

        // GitHub does not supply an email_verified flag — isEmailVerifiedByOAuthProvider returns false.
        OAuth::fake(data: ['email' => 'newuser@example.com']);

        $expectedResponse = $this->expectOAuthResponseInstance(
            TestOAuthResponse::forOAuthErrorResponse(OAuthError::EmailVerificationRequired),
            abstract: ErrorResponse::class
        );

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertGuest();
    }

    /**
     * Scenario 3: Registration is enabled. Email verification is required.
     * User does not exist. Provider is Google and indicates the email is verified.
     *
     * Expected: A new user is registered, the registrar marks their email as verified
     * because Google confirms it, canLink passes, and authentication succeeds.
     */
    public function test_registration_with_google_verified_email_marks_email_verified_and_succeeds(): void
    {
        config(['oauth.email_verification_required' => true]);

        OAuth::fake('google', data: [
            'email' => 'newuser@example.com',
            'email_verified' => true,
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(AuthenticateResponse::class);

        $response = $this->get(route('oauth.callback', ['client' => 'google']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertOAuthAccountExists('google');

        // Confirm the registrar called markEmailAsVerified() on the newly created user.
        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($newUser, 'A new user should have been created via OAuth registration.');
        $this->assertNotNull(
            $newUser->email_verified_at,
            'The registrar should mark the email as verified when Google confirms it.'
        );
    }

    /**
     * Scenario 4: Email verification is required. User exists and is already linked
     * to Google. User signs in via Google OAuth, but Google does NOT indicate the email
     * is verified (email_verified = false). The user's own email_verified_at is also null.
     *
     * Expected: Authentication succeeds — the canLogin gate does not enforce email
     * verification, so already-linked users can always log in.
     */
    public function test_already_linked_unverified_user_can_login_despite_email_verification_required(): void
    {
        config(['oauth.email_verification_required' => true]);

        OAuth::fake('google', data: [
            'email' => 'linkeduser@example.com',
            'email_verified' => false,
        ]);

        $user = User::factory()->unverified()->create([
            'email' => 'linkeduser@example.com',
            'password' => null,
        ]);

        $this->connectOAuthAccount($user, 'google');

        $expectedResponse = $this->expectOAuthResponseInstance(AuthenticateResponse::class);

        $response = $this->get(route('oauth.callback', ['client' => 'google']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }

    /**
     * Scenario 5: Email verification is required. User exists and is already linked
     * to Google. User signs in via Google OAuth and Google confirms the email is verified.
     *
     * Expected: Authentication succeeds as normal.
     */
    public function test_already_linked_verified_user_can_login_with_email_verification_required(): void
    {
        config(['oauth.email_verification_required' => true]);

        OAuth::fake('google', data: [
            'email' => 'linkeduser@example.com',
            'email_verified' => true,
        ]);

        $user = User::factory()->create([
            'email' => 'linkeduser@example.com',
            'password' => null,
        ]);

        $this->connectOAuthAccount($user, 'google');

        $expectedResponse = $this->expectOAuthResponseInstance(AuthenticateResponse::class);

        $response = $this->get(route('oauth.callback', ['client' => 'google']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
    }

    /**
     * Scenario 6: Email verification is required. User exists but is not yet linked
     * to any OAuth provider. The user's email is unverified. Provider is GitHub, which
     * does not indicate email verification.
     *
     * Expected: The existing user is resolved by email, but canLink blocks because the
     * user is a MustVerifyEmail implementor whose email_verified_at is null →
     * EmailVerificationRequired error.
     */
    public function test_unlinked_unverified_user_cannot_link_when_email_verification_required(): void
    {
        config(['oauth.email_verification_required' => true]);

        OAuth::fake(data: ['email' => 'existing@example.com']);

        // Password must be null — if set, canLink would throw MustLoginToLink instead.
        User::factory()->unverified()->create([
            'email' => 'existing@example.com',
            'password' => null,
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(
            TestOAuthResponse::forOAuthErrorResponse(OAuthError::EmailVerificationRequired),
            abstract: ErrorResponse::class
        );

        $response = $this->get(route('oauth.callback', ['client' => 'github']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertGuest();
    }

    /**
     * Scenario 7: Email verification is required. User exists but is not yet linked
     * to any OAuth provider. The user's email is already verified (default factory state).
     * Provider is Google and confirms the email is verified.
     *
     * Expected: canLink passes because the user has a verified email_verified_at,
     * the OAuth account is linked, and authentication succeeds.
     */
    public function test_unlinked_verified_user_can_link_and_login_when_email_verification_required(): void
    {
        config(['oauth.email_verification_required' => true]);

        OAuth::fake('google', data: [
            'email' => 'existing@example.com',
            'email_verified' => true,
        ]);

        // Factory default sets email_verified_at = now(), so this user is already verified.
        $user = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => null,
        ]);

        $expectedResponse = $this->expectOAuthResponseInstance(AuthenticateResponse::class);

        $response = $this->get(route('oauth.callback', ['client' => 'google']));

        $this->assertOAuthResponseInstanceReturned($response, $expectedResponse);
        $this->assertOAuthAccountAssociated($user, 'google');
    }
}
