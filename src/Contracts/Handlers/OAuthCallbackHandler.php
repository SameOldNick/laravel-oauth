<?php

namespace SameOldNick\OAuth\Contracts\Handlers;

use SameOldNick\OAuth\Clients\Client;
use Laravel\Socialite\Contracts\User as SocialUser;

interface OAuthCallbackHandler
{
    /**
     * Handles OAuth callback
     *
     * @return mixed
     */
    public function handleCallback(Client $client, SocialUser $socialUser);
}
