<?php

namespace Workbench\App\OAuth\Fortify\Responses;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Fortify;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Responses\LoggedInResponse as LoggedInResponseContract;
use SameOldNick\OAuth\Events\OAuthSignedIn;

class LoggedInResponse implements LoggedInResponseContract
{
    /**
     * {@inheritDoc}
     */
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        OAuthSignedIn::dispatch($user, $client->clientName());
        $fallback = config('oauth.routes.redirects.success')
            ? route(config('oauth.routes.redirects.success'))
            : Fortify::redirects('login');

        return redirect()->intended($fallback);
    }
}
