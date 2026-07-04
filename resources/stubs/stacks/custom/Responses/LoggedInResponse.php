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

        $fallback = config('oauth.routes.redirects.success')
            ? route(config('oauth.routes.redirects.success'))
            : '/';

        return redirect()
            ->intended($fallback)
            ->with('success', __('oauth::messages.successfully_signed_in', ['provider' => $client->getName()]));
    }
}
