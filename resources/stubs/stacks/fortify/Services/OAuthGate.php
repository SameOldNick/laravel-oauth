<?php

namespace VendorName\OAuth\Fortify\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Features;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Responses\Errors;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState;
use SameOldNick\OAuth\Contracts\Services\OAuthGate as OAuthGateContract;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver;
use SameOldNick\OAuth\Exceptions\OAuthGateFailureException;
use SameOldNick\OAuth\Support\ConfigHelper;

class OAuthGate implements OAuthGateContract
{
    public function __construct(
        protected OAuthAuthenticationState $authenticationState,
        protected OAuthUserResolver $userResolver
    ) {
        //
    }

    /**
     * Determine if the given OAuth profile is eligible for registration.
     */
    public function canRegister(Client $client, SocialUser $socialUser): bool
    {
        if (! Features::enabled(Features::registration())) {
            return false;
        }

        $userModel = ConfigHelper::getUserModel();
        $emailField = ConfigHelper::getUserEmailField();

        $query = $userModel::query();

        if ($this->isUserSoftDeletable()) {
            $query->withTrashed();
        }

        $user = $query->where($emailField, $socialUser->getEmail())->first();

        if ($user) {
            if ($this->isUserSoftDeletable() && $user->trashed()) {
                // Email exists but user is soft-deleted
                Log::warning('OAuth login attempt for soft-deleted user', [
                    'email' => $socialUser->getEmail(),
                    'provider' => $client->clientName(),
                ]);

                OAuthGateFailureException::throwWithResponse(fn () => $this->userTrashedResponse($client, $socialUser, $user));
            } else {
                // Email exists, user is active - cannot register because email is already taken
                Log::info('OAuth login attempt with existing email', [
                    'email' => $socialUser->getEmail(),
                    'provider' => $client->clientName(),
                ]);

                OAuthGateFailureException::throwWithResponse(fn () => $this->mustLoginToLinkResponse($client, $socialUser, $user));
            }
        }

        return true;
    }

    /**
     * Determine if the given OAuth profile can be linked to an existing user.
     */
    public function canLink(Client $client, SocialUser $socialUser, Authenticatable $user): bool
    {
        // If user has a password, they must log in to link (prevents account takeover if email is already registered but not linked)
        if (! $this->authenticationState->isLoggedIn() && $user->password !== null) {
            // Email exists and has a password
            OAuthGateFailureException::throwWithResponse(
                fn () => $this->mustLoginToLinkResponse($client, $socialUser, $user),
                'You must log in to link this OAuth account.'
            );
        }

        // Check if social user is already linked to another account
        $linkedUser = $this->userResolver->resolveLinkedUser($client, $socialUser);

        if ($linkedUser && (string) $linkedUser->getAuthIdentifier() !== (string) $user->getAuthIdentifier()) {
            OAuthGateFailureException::throwWithResponse(
                fn () => $this->alreadyLinkedErrorResponse($client, $socialUser, $user),
                'This OAuth account is already linked to another user.'
            );
        }

        return true;
    }

    /**
     * Determine if the given OAuth profile can be used to log in.
     */
    public function canLogin(Client $client, SocialUser $socialUser, Authenticatable $user): bool
    {
        return true;
    }

    /**
     * Determine if the given OAuth profile can be unlinked from a user.
     */
    public function canUnlink(Client $client, SocialUser $socialUser, Authenticatable $user): bool
    {
        return true;
    }

    /**
     * Checks if users model is soft deletable
     */
    protected function isUserSoftDeletable(): bool
    {
        $userModel = ConfigHelper::getUserModel();

        return in_array(SoftDeletes::class, class_uses($userModel), true);
    }

    /**
     * Response when user must log in to link OAuth (email exists but not linked)
     */
    protected function mustLoginToLinkResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $response = app(Errors\MustLoginToLinkResponse::class)->create($client, $socialUser, $user);

        return $response;
    }

    /**
     * Response when user is soft-deleted
     */
    protected function userTrashedResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $response = app(Errors\UserTrashedResponse::class)->create($client, $socialUser, $user);

        return $response;
    }

    /**
     * Response when OAuth account is already linked to another user
     */
    protected function alreadyLinkedErrorResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $response = app(Errors\AlreadyLinkedErrorResponse::class)->create($client, $socialUser, $user);

        return $response;
    }
}
