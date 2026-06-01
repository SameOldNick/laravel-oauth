<?php

namespace VendorName\OAuth\Custom\Responses;

use Illuminate\Contracts\Auth\Authenticatable;
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

        return redirect()
            ->intended('/')
            ->with('success', __('oauth::messages.logged_in_successfully', ['provider' => $client->getName()]));
    }
}
