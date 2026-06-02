<?php

namespace SameOldNick\OAuth\Contracts\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;

interface OAuthAccountAssociator
{
    /**
     * Associate an OAuth account with the provided user.
     */
    public function associate(Client $client, Authenticatable $user, SocialUser $socialUser): void;
}
