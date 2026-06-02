<?php

namespace SameOldNick\OAuth\Contracts\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;

interface OAuthUserRegistrar
{
    /**
     * Register a new application user from an OAuth profile.
     */
    public function register(Client $client, SocialUser $socialUser): Authenticatable;
}
