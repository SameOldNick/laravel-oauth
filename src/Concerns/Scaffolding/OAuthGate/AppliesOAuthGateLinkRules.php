<?php

namespace SameOldNick\OAuth\Concerns\Scaffolding\OAuthGate;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Enums\OAuthError;
use SameOldNick\OAuth\Exceptions\OAuthGateFailureException;

trait AppliesOAuthGateLinkRules
{
    /**
     * Determine if the given OAuth profile can be linked to an existing user.
     */
    public function canLink(Client $client, SocialUser $socialUser, Authenticatable $user): bool
    {
        // If user has a password, they must log in to link (prevents account takeover if email is already registered but not linked)
        $this->checkMustLoginToLink($client, $socialUser, $user);

        // Check if social user is already linked to another account
        $this->checkAlreadyLinked($client, $socialUser, $user);

        $this->checkEmailVerificationRequiredToLink($client, $socialUser, $user);

        return true;
    }

    /**
     * Determine if the given OAuth profile can be unlinked from a user.
     */
    public function canUnlink(Client $client, SocialUser $socialUser, Authenticatable $user): bool
    {
        return true;
    }

    protected function checkMustLoginToLink(Client $client, SocialUser $socialUser, Authenticatable $user): void
    {
        if (! $this->authenticationState->isLoggedIn() && $this->userHasPassword($user)) {
            // Email exists and has a password
            OAuthGateFailureException::throwWithResponse(
                fn () => $this->createErrorResponse(OAuthError::MustLoginToLink, $client, $socialUser, $user),
                'You must log in to link this OAuth account.'
            );
        }
    }

    protected function checkAlreadyLinked(Client $client, SocialUser $socialUser, Authenticatable $user): void
    {
        // Check if social user is already linked to another account
        $linkedUser = $this->userResolver->resolveLinkedUser($client, $socialUser);

        if ($linkedUser && (string) $linkedUser->getAuthIdentifier() !== (string) $user->getAuthIdentifier()) {
            OAuthGateFailureException::throwWithResponse(
                fn () => $this->createErrorResponse(OAuthError::AlreadyLinked, $client, $socialUser, $user),
                'This OAuth account is already linked to another user.'
            );
        }
    }

    protected function checkEmailVerificationRequiredToLink(Client $client, SocialUser $socialUser, Authenticatable $user): void
    {
        if (config('oauth.email_verification_required')) {
            if ($user instanceof MustVerifyEmail && ! $user->hasVerifiedEmail()) {
                OAuthGateFailureException::throwWithResponse(
                    fn () => $this->createErrorResponse(OAuthError::EmailVerificationRequired, $client, $socialUser, $user),
                    'You must verify your email before linking this OAuth account.'
                );
            }
        }
    }

    protected function userHasPassword(Authenticatable $user): bool
    {
        return $user->password !== null;
    }
}
