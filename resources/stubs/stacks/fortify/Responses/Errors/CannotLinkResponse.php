<?php

namespace VendorName\OAuth\Fortify\Responses\Errors;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Responses\Errors\CannotLinkResponse as ErrorResponsesContract;

class CannotLinkResponse implements ErrorResponsesContract
{
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        return redirect()
            ->route('login')
            ->with('error', __('oauth::messages.cannot_link', ['provider' => $client->getName()]));
    }
}
