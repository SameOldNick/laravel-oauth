<?php

namespace SameOldNick\OAuth\Contracts\Services;

use SameOldNick\OAuth\Clients\Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;

interface OAuthGate
{
    /**
     * Determine if the given OAuth profile is eligible for registration.
     */
    public function canRegister(Client $client, SocialUser $socialUser): bool;

    /**
     * Determine if the given OAuth profile can be linked to an existing user.
     */
    public function canLink(Client $client, SocialUser $socialUser, Authenticatable $user): bool;

    /**
     * Determine if the given OAuth profile can be used to log in.
     */
    public function canLogin(Client $client, SocialUser $socialUser, Authenticatable $user): bool;

    /**
     * Determine if the given OAuth profile can be unlinked from a user.
     */
    public function canUnlink(Client $client, SocialUser $socialUser, Authenticatable $user): bool;
}
