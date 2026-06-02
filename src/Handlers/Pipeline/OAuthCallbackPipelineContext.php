<?php

namespace SameOldNick\OAuth\Handlers\Pipeline;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;

class OAuthCallbackPipelineContext
{
    /**
     * Initializes the pipeline context with necessary data.
     *
     * @param  Client  $client  The OAuth client being used
     * @param  SocialUser  $socialUser  The user information from the OAuth provider
     * @param  Authenticatable|null  $user  The user associated with the OAuth account (if any)
     * @param  Authenticatable|null  $currentUser  The currently authenticated user (if any)
     */
    public function __construct(
        private Client $client,
        private ?Authenticatable $user,
        private SocialUser $socialUser,
        private ?Authenticatable $currentUser = null,
    ) {
        //
    }

    /**
     * Gets the Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Gets the User associated with the OAuth account (if any)
     */
    public function getUser(): ?Authenticatable
    {
        return $this->user;
    }

    /**
     * Sets the User associated with the OAuth account
     */
    public function setUser(?Authenticatable $user): void
    {
        $this->user = $user;
    }

    /**
     * Gets the SocialUser from the OAuth provider
     */
    public function getSocialUser(): SocialUser
    {
        return $this->socialUser;
    }

    /**
     * Gets the currently authenticated user (if any)
     */
    public function getCurrentUser(): ?Authenticatable
    {
        return $this->currentUser;
    }
}
