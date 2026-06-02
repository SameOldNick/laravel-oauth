<?php

namespace SameOldNick\OAuth\Concerns;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse;
use SameOldNick\OAuth\Contracts\Responses\ErrorResponse;
use SameOldNick\OAuth\Contracts\Responses\LoggedInResponse;
use SameOldNick\OAuth\Enums\OAuthError;

trait CreatesConnectedAccountResponses
{
    /**
     * Authenticate response
     *
     * @return void
     */
    protected function createAuthenticateResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $response = app(AuthenticateResponse::class)->create($client, $socialUser, $user);

        return $response;
    }

    /**
     * Response when user is logged in
     */
    protected function createLoggedInResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $response = app(LoggedInResponse::class)->create($client, $socialUser, $user);

        return $response;
    }

    /**
     * Creates error response
     */
    protected function createErrorResponse(OAuthError $error, Client $client, SocialUser $socialUser, ?Authenticatable $user = null)
    {
        $response = app(ErrorResponse::class)->create($error, $client, $socialUser, $user);

        return $response;
    }
}
