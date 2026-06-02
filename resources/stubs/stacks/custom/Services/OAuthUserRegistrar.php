<?php

namespace VendorName\OAuth\Custom\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Concerns\Scaffolding\HandlesOAuthUserRegistration;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar as OAuthUserRegistrarContract;
use SameOldNick\OAuth\Support\ConfigHelper;

class OAuthUserRegistrar implements OAuthUserRegistrarContract
{
    use HandlesOAuthUserRegistration;

    /**
     * {@inheritDoc}
     */
    public function register(Client $client, SocialUser $socialUser): Authenticatable
    {
        $userModel = ConfigHelper::getUserModel();

        $newUser = $userModel::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            // Set password to null since they won't be using it to log in, and to indicate that the account was created via OAuth.
            // They can set a password later if they want to enable password login.
            'password' => null,
        ]);

        return $this->finalizeOAuthRegistration($client, $socialUser, $newUser);
    }
}
