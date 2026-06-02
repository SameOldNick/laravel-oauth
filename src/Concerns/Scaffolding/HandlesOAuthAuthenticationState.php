<?php

namespace SameOldNick\OAuth\Concerns\Scaffolding;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

trait HandlesOAuthAuthenticationState
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
