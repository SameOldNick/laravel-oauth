<?php

namespace SameOldNick\OAuth\Concerns\Scaffolding;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;

trait CreatesAuthenticatedOAuthResponses
{
    /**
     * Shared authenticated response flow: log in, then delegate to LoggedInResponse.
     */
    protected function createAuthenticatedResponse(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        $this->login($user);

        return $this->loggedInResponse->create($client, $socialUser, $user);
    }

    /**
     * Login user.
     *
     * @return $this
     */
    protected function login(Authenticatable $user): static
    {
        Auth::login($user);

        return $this;
    }
}
