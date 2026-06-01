<?php

namespace VendorName\OAuth\Custom\Services;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar as OAuthUserRegistrarContract;
use SameOldNick\OAuth\Support\ConfigHelper;

class OAuthUserRegistrar implements OAuthUserRegistrarContract
{
    /**
     * {@inheritDoc}
     */
    public function register(SocialUser $socialUser): Authenticatable
    {
        $userModel = ConfigHelper::getUserModel();

        $newUser = $userModel::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            // Set password to null since they won't be using it to log in, and to indicate that the account was created via OAuth.
            // They can set a password later if they want to enable password login.
            'password' => null,
        ]);

        // Needs to fire so things like emails can be sent.
        event(new Registered($newUser));

        if (request()->hasSession()) {
            session()->regenerate();
        }

        return $newUser;
    }
}
