<?php

namespace SameOldNick\OAuth\Contracts\Handlers;

use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;

interface OAuthCallbackHandler
{
    /**
     * Handles OAuth callback
     *
     * @return mixed
     */
    public function handleCallback(Client $client, SocialUser $socialUser);
}
