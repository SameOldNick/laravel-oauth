<?php

namespace SameOldNick\OAuth\Contracts\Responses;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;

interface LoggedInResponse
{
    /**
     * Create a response indicating the user has been logged in successfully.
     *
     * @param  Client  $client  The OAuth client that was used for authentication.
     * @param  SocialUser  $socialUser  The social user information returned by the OAuth provider.
     * @param  Authenticatable  $user  The user that has been logged in.
     * @return mixed A response that can be returned to the user, such as a redirect or a view.
     */
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user);
}
