<?php

namespace SameOldNick\OAuth\Contracts\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;

interface OAuthUserResolver
{
    /**
     * Resolve the currently linked application user for the provider identity.
     */
    public function resolveLinkedUser(Client $client, SocialUser $socialUser): ?Authenticatable;

    /**
     * Resolve an existing user by email (if supported by the provider) to link to.
     *
     * This is used for the "registration" scenario where a user tries to authenticate with an OAuth provider that supports email retrieval, and we want to check if there's an existing account with the same email to link to instead of creating a new account.
     */
    public function resolveExistingUserByEmail(Client $client, SocialUser $socialUser): ?Authenticatable;
}
