<?php

namespace SameOldNick\OAuth\Contracts\Responses;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Enums\OAuthError;

interface ErrorResponse
{
    /**
     * Create an error response based on the given error type and context.
     *
     * @param  OAuthError  $error  The type of error that occurred.
     * @param  Client  $client  The OAuth client involved in the error.
     * @param  SocialUser  $socialUser  The social user profile related to the error.
     * @param  Authenticatable|null  $user  The authenticated user, if available (can be null).
     * @return RedirectResponse|Response The response to be returned to the user.
     */
    public function create(OAuthError $error, Client $client, SocialUser $socialUser, ?Authenticatable $user = null);
}
