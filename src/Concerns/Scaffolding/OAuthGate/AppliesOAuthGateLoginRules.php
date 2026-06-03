<?php

namespace SameOldNick\OAuth\Concerns\Scaffolding\OAuthGate;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;

trait AppliesOAuthGateLoginRules
{
    /**
     * Determine if the given OAuth profile can be used to log in.
     */
    public function canLogin(Client $client, SocialUser $socialUser, Authenticatable $user): bool
    {
        return true;
    }
}
