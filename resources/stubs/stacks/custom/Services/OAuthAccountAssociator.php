<?php

namespace VendorName\OAuth\Custom\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Services\OAuthAccountAssociator as OAuthAccountAssociatorContract;
use SameOldNick\OAuth\Events\AccountConnected;
use SameOldNick\OAuth\Models\OAuthProvider;
use SameOldNick\OAuth\Support\ConfigHelper;

class OAuthAccountAssociator implements OAuthAccountAssociatorContract
{
    /**
     * {@inheritDoc}
     */
    public function associate(Client $client, Authenticatable $user, SocialUser $socialUser, bool $replace = false): void
    {
        if ($replace) {
            $oauthProviderModel = ConfigHelper::getConnectedAccountModel();

            $oauthProviderModel::where('user_id', $user->getAuthIdentifier())
                ->where('provider_name', $client->clientName())
                ->delete();
        }

        $oauthProvider = $this->mapToOAuthProvider($client, $socialUser);
        $oauthProvider->user()->associate($user);
        $oauthProvider->save();

        AccountConnected::dispatch($user, $client->clientName());
    }

    /**
     * Create an OAuthProvider model from social user data.
     */
    protected function mapToOAuthProvider(Client $client, SocialUser $socialUser): OAuthProvider
    {
        $oauthProvider = new OAuthProvider;
        $oauthProvider->provider_name = $client->clientName();
        $oauthProvider->provider_id = $socialUser->getId();
        $oauthProvider->access_token = $socialUser->token;
        $oauthProvider->refresh_token = $socialUser->refreshToken;
        $oauthProvider->avatar_url = $socialUser->getAvatar();
        $oauthProvider->expires_at = now()->addSeconds($socialUser->expiresIn);

        return $oauthProvider;
    }
}
