<?php

namespace SameOldNick\OAuth\Contracts\Services;

use SameOldNick\OAuth\Clients\Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;

interface OAuthAccountAssociator
{
    /**
     * Associate an OAuth account with the provided user.
     */
    public function associate(Client $client, Authenticatable $user, SocialUser $socialUser, bool $replace = false): void;
}
