<?php

namespace SameOldNick\OAuth\Contracts\Handlers;

use SameOldNick\OAuth\Clients\Client;

interface OAuthRedirectHandler
{
    /**
     * Handles OAuth redirect
     *
     * @return mixed
     */
    public function handleRedirect(Client $client);
}
