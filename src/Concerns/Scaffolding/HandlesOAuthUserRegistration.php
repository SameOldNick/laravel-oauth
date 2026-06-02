<?php

namespace SameOldNick\OAuth\Concerns\Scaffolding;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;

trait HandlesOAuthUserRegistration
{
    /**
     * Apply common post-registration behavior for OAuth-created users.
     */
    protected function finalizeOAuthRegistration(Client $client, SocialUser $socialUser, Authenticatable $newUser): Authenticatable
    {
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
     */
    protected function isEmailVerificationRequired(): bool
    {
        return config('oauth.email_verification_required', false);
    }

    /**
     * Determines if the email provided by the OAuth provider can be considered verified based on the provider's data and behavior.
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
