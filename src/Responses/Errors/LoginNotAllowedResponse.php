<?php

namespace SameOldNick\OAuth\Responses\Errors;

use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Responses\Errors\LoginNotAllowedResponse as ErrorResponsesContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;

class LoginNotAllowedResponse implements ErrorResponsesContract
{
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        return redirect()
            ->route('login')
            ->with('error', __('oauth::messages.login_not_allowed', ['provider' => $client->getName()]));
    }
}
