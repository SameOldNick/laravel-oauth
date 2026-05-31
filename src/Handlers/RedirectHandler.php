<?php

namespace SameOldNick\OAuth\Handlers;

use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Handlers\OAuthRedirectHandler;
use Illuminate\Http\Request;

class RedirectHandler implements OAuthRedirectHandler
{
    public function __construct(
        public readonly Request $request
    ) {
        //
    }

    /**
     * Handles OAuth redirect
     *
     * @return mixed
     */
    public function handleRedirect(Client $client)
    {
        $client->prepareRedirect();

        // Set redirect URL (since it will be missing)
        $client->provider()->redirectUrl(route('oauth.callback', ['client' => $client->clientName()]));

        return $client->provider()->redirect();
    }
}
