<?php

namespace VendorName\OAuth\Fortify\Services;

use Illuminate\Auth\Events\Registered;
use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar as OAuthUserRegistrarContract;

class OAuthUserRegistrar implements OAuthUserRegistrarContract
{
    public function __construct(
        protected readonly CreatesNewUsers $userCreator,
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function register(SocialUser $socialUser): Authenticatable
    {
        // Re-use Laravel\Fortify's user creation logic to ensure things likeevents are properly handled.
        // We skip validation since OAuth users won't be providing a password during registration. They can set one later if they want to enable password login.
        $newUser = $this->userCreator->skipPasswordValidation()->create([
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
