<?php

namespace VendorName\OAuth\Custom\Responses\Errors;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Responses\Errors\AlreadyLinkedErrorResponse as ErrorResponsesContract;

class AlreadyLinkedErrorResponse implements ErrorResponsesContract
{
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        return back()->with('error', __('oauth::messages.already_linked', ['provider' => $client->getName()]));
    }
}
