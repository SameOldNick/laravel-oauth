<?php

namespace VendorName\OAuth\Custom\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState as OAuthAuthenticationStateContract;

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
