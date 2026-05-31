<?php

namespace SameOldNick\OAuth\Responses\Errors;

use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Responses\Errors\AlreadyLinkedErrorResponse as ErrorResponsesContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;

class AlreadyLinkedErrorResponse implements ErrorResponsesContract
{
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        return redirect()
            ->route('profile.edit')
            ->with('error', __('oauth::messages.already_linked', ['provider' => $client->getName()]));
    }
}
