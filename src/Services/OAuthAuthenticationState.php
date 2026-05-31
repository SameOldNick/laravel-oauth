<?php

namespace SameOldNick\OAuth\Services;

use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState as OAuthAuthenticationStateContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

class OAuthAuthenticationState implements OAuthAuthenticationStateContract
{
    /**
     * {@inheritDoc}
     */
    public function isLoggedIn(): bool
    {
        return Auth::check();
    }

    /**
     * {@inheritDoc}
     */
    public function currentUser(): Authenticatable
    {
        return Auth::user();
    }
}
