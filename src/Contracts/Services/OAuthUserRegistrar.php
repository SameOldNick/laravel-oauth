<?php

namespace SameOldNick\OAuth\Contracts\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;

interface OAuthUserRegistrar
{
    /**
     * Register a new application user from an OAuth profile.
     */
    public function register(SocialUser $socialUser): Authenticatable;
}
