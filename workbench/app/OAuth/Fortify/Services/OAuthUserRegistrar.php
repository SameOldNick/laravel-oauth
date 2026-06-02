<?php

namespace Workbench\App\OAuth\Fortify\Services;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar as OAuthUserRegistrarContract;

class OAuthUserRegistrar implements OAuthUserRegistrarContract
{
    public function __construct(
        protected readonly CreatesNewUsers $userCreator,
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function register(Client $client, SocialUser $socialUser): Authenticatable
    {
        // Re-use Laravel\Fortify's user creation logic to ensure things like events are properly handled.
        // We skip validation since OAuth users won't be providing a password during registration. They can set one later if they want to enable password login.
        $newUser = $this->userCreator->skipPasswordValidation()->create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            // Set password to null since they won't be using it to log in, and to indicate that the account was created via OAuth.
            // They can set a password later if they want to enable password login.
            'password' => null,
        ]);

        // Guard against user creators/casts that may still persist a non-null password.
        if ($newUser->password !== null) {
            $newUser->forceFill(['password' => null])->save();
            $newUser->refresh();
        }

        if ($this->isEmailVerificationRequired() &&
            $newUser instanceof MustVerifyEmail &&
            $this->isEmailVerifiedByOAuthProvider($client, $socialUser)) {
            $newUser->markEmailAsVerified();
        }

        // Needs to fire so things like emails can be sent.
        event(new Registered($newUser));

        if (request()->hasSession()) {
            session()->regenerate();
        }

        return $newUser;
    }

    /**
     * Determines if email verification is required for users registering via OAuth.
     *
     * This checks the application configuration to see if email verification is required for OAuth registrations. If this returns true, then after creating the user, we will check if the email provided by the OAuth provider can be considered verified (based on the provider's guarantees and fields) and mark it as verified if so.
     *
     * @return bool True if email verification is required for OAuth registrations, false otherwise.
     */
    protected function isEmailVerificationRequired(): bool
    {
        return config('oauth.email_verification_required', false);
    }

    /**
     * Determines if the email provided by the OAuth provider can be considered verified based on the provider's data and behavior.
     *
     * Since different providers have different guarantees and fields regarding email verification, we check based on the provider:
     * - For providers that guarantee verified emails (e.g. Facebook), we can trust that the email is verified.
     * - For providers that provide a specific field indicating verification (e.g. Google), we check that field.
     * - For providers that do not guarantee or indicate email verification (e.g. GitHub, Twitter), we assume the email is not verified to be safe.
     *
     * @param  Client  $client  The OAuth client being used for authentication.
     * @param  SocialUser  $socialUser  The user information returned by the OAuth provider.
     * @return bool True if the email can be considered verified, false otherwise.
     */
    protected function isEmailVerifiedByOAuthProvider(Client $client, SocialUser $socialUser): bool
    {
        return match ($client->clientName()) {
            // TODO: Use GitHub /user/emails endpoint to check if email is verified, since the main /user endpoint does not guarantee that the email is verified. For now, we assume it's not verified to be safe.
            'github' => false, // The GitHub /user API endpoint does not guarantee that the email is verified.
            'google' => $socialUser['email_verified'] ?? false, // Google provides an 'email_verified' field we can check.
            'facebook' => true, // Facebook only returns verified emails, so we can trust that it's verified.
            'twitter' => false, // No guarantee that Twitter emails are verified, and they don't provide a way to check, so we assume it's not verified.
            default => false, // For other providers, we can't be sure, so we assume it's not verified.
        };
    }
}
