<?php

namespace SameOldNick\OAuth\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver as OAuthUserResolverContract;
use SameOldNick\OAuth\Support\ConfigHelper;

class OAuthUserResolver implements OAuthUserResolverContract
{
    /**
     * {@inheritDoc}
     */
    public function resolveLinkedUser(Client $client, SocialUser $socialUser): ?Authenticatable
    {
        $oauthProviderModel = ConfigHelper::getConnectedAccountModel();

        $providerModel = $oauthProviderModel::where('provider_id', $socialUser->getId())
            ->where('provider_name', $client->clientName())
            ->first();

        // $providerModel->user will be null if user is deleted.
        return $providerModel && $providerModel->user ? $providerModel->user : null;
    }

    /**
     * {@inheritDoc}
     */
    public function resolveExistingUserByEmail(Client $client, SocialUser $socialUser): ?Authenticatable
    {
        $email = $socialUser->getEmail();

        if (! $email) {
            return null;
        }

        $userModel = ConfigHelper::getUserModel();
        $emailField = ConfigHelper::getUserEmailField();

        return $userModel::where($emailField, $email)->first();
    }
}
