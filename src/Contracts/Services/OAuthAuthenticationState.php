<?php

namespace SameOldNick\OAuth\Contracts\Services;

use Illuminate\Contracts\Auth\Authenticatable;

interface OAuthAuthenticationState
{
    /**
     * Determine whether an application user is currently authenticated.
     */
    public function isLoggedIn(): bool;

    /**
     * Get the currently authenticated user.
     */
    public function currentUser(): Authenticatable;
}
