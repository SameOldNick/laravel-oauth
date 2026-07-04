<?php

namespace SameOldNick\OAuth\Concerns\Scaffolding;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Enums\OAuthError;

trait CreatesOAuthErrorResponses
{
    /**
     * Get the URL to redirect to on error. Checks the config for a named route
     * first, then falls back to the intended URL stored in the session.
     */
    protected function getErrorRedirectUrl(): string
    {
        $redirectError = config('oauth.routes.redirects.error');

        if ($redirectError) {
            return route($redirectError);
        }

        return session()->pull('oauth.intended_url', url()->previous());
    }

    /**
     * Generate a user-friendly error message based on the error type and context.
     */
    protected function getErrorMessage(OAuthError $error, Client $client, SocialUser $socialUser, ?Authenticatable $user = null): string
    {
        // Generate error message based on the error type and context
        return match ($error) {
            OAuthError::RegistrationNotAllowed => __('oauth::messages.registration_not_allowed', ['provider' => $client->getName()]),
            OAuthError::MustLoginToLink => __('oauth::messages.must_login_to_link', ['provider' => $client->getName()]),
            OAuthError::AlreadyLinked => __('oauth::messages.already_linked', ['provider' => $client->getName()]),
            OAuthError::CannotLink => __('oauth::messages.cannot_link', ['provider' => $client->getName()]),
            OAuthError::LoginNotAllowed => __('oauth::messages.login_not_allowed', ['provider' => $client->getName()]),
            OAuthError::UserTrashed => __('oauth::messages.user_trashed', ['provider' => $client->getName()]),
            default => __('oauth::messages.unknown_error', ['provider' => $client->getName()]),
        };
    }
}
