<?php

namespace SameOldNick\OAuth\Contracts\Responses\Errors;

use SameOldNick\OAuth\Clients\Client;
use Laravel\Socialite\Contracts\User as SocialUser;

interface RegistrationNotAllowedResponse
{
    public function create(Client $client, SocialUser $socialUser);
}
