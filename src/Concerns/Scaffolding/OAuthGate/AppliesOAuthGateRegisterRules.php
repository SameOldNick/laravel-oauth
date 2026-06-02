<?php

namespace SameOldNick\OAuth\Concerns\Scaffolding\OAuthGate;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Enums\OAuthError;
use SameOldNick\OAuth\Exceptions\OAuthGateFailureException;
use SameOldNick\OAuth\Support\ConfigHelper;

trait AppliesOAuthGateRegisterRules
{
    /**
     * Create an OAuth error response.
     */
    abstract protected function createErrorResponse(
        OAuthError $error,
        Client $client,
        SocialUser $socialUser,
        ?Authenticatable $user = null
    );

    /**
     * Determine if the given OAuth profile is eligible for registration.
     */
    public function canRegister(Client $client, SocialUser $socialUser): bool
    {
        if (! $this->isRegistrationAllowed($client, $socialUser)) {
            return false;
        }

        $this->checkUserAlreadyExists($client, $socialUser);

        return true;
    }

    /**
     * Determine if registration is allowed for the given OAuth profile.
     */
    protected function isRegistrationAllowed(Client $client, SocialUser $socialUser): bool
    {
        return config('oauth.allow_registration');
    }

    /**
     * Check if a user with the same email already exists and handle accordingly.
     */
    protected function checkUserAlreadyExists(Client $client, SocialUser $socialUser): void
    {
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

                OAuthGateFailureException::throwWithResponse(fn () => $this->createErrorResponse(OAuthError::UserTrashed, $client, $socialUser, $user));
            } else {
                // Email exists, user is active - cannot register because email is already taken
                Log::info('OAuth login attempt with existing email', [
                    'email' => $socialUser->getEmail(),
                    'provider' => $client->clientName(),
                ]);

                OAuthGateFailureException::throwWithResponse(fn () => $this->createErrorResponse(OAuthError::MustLoginToLink, $client, $socialUser, $user));
            }
        }
    }

    /**
     * Checks if users model is soft deletable
     */
    protected function isUserSoftDeletable(): bool
    {
        $userModel = ConfigHelper::getUserModel();

        return in_array(SoftDeletes::class, class_uses($userModel), true);
    }
}
