<?php

namespace VendorName\OAuth\Custom\Responses;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Concerns\Scaffolding\CreatesAuthenticatedOAuthResponses;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse as AuthenticateResponseContract;

class AuthenticateResponse implements AuthenticateResponseContract
{
    use CreatesAuthenticatedOAuthResponses;

    public function __construct(
        protected readonly LoggedInResponse $loggedInResponse,
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        return $this->createAuthenticatedResponse($client, $socialUser, $user);
    }
}
