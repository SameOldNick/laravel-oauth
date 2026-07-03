<?php

namespace SameOldNick\OAuth\Handlers;

use Illuminate\Http\Request;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Handlers\OAuthRedirectHandler;

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

        // Store the intended page URL so the callback can redirect back here on error,
        // rather than using back() which would return to the OAuth provider's site
        // and cause an infinite redirect loop.
        session()->put('oauth.intended_url', url()->previous());

        // Set redirect URL (since it will be missing)
        $client->provider()->redirectUrl(route('oauth.callback', ['client' => $client->clientName()]));

        return $client->provider()->redirect();
    }
}
