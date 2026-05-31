<?php

namespace SameOldNick\OAuth\Contracts\Responses\Errors;

use SameOldNick\OAuth\Clients\Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;

interface AlreadyLinkedErrorResponse
{
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user);
}
