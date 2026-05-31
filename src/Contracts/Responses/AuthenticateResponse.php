<?php

namespace SameOldNick\OAuth\Contracts\Responses;

use SameOldNick\OAuth\Clients\Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;

interface AuthenticateResponse
{
    /**
     * Create a response indicating that the user may need to complete two-factor authentication.
     *
     * @param  Client  $client  The OAuth client that was used for authentication.
     * @param  SocialUser  $socialUser  The social user information returned by the OAuth provider.
     * @param  Authenticatable  $user  The user that may need to complete two-factor authentication.
     * @return mixed A response that can be returned to the user, such as a redirect or a view.
     */
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user);
}
