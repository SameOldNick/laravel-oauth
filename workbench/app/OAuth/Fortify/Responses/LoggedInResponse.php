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

        // toast(__('oauth::messages.successfully_signed_in', ['provider' => $client->getName()]), 'success');

        return redirect()->intended(Fortify::redirects('login'));
    }
}
