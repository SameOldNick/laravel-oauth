<?php

namespace VendorName\OAuth\Fortify\Responses;

use Illuminate\Contracts\Auth\Authenticatable;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Concerns\Scaffolding\CreatesOAuthErrorResponses;
use SameOldNick\OAuth\Contracts\Responses\ErrorResponse as ErrorResponseContract;
use SameOldNick\OAuth\Enums\OAuthError;

class ErrorResponse implements ErrorResponseContract
{
    use CreatesOAuthErrorResponses;

    /**
     * {@inheritDoc}
     */
    public function create(OAuthError $error, Client $client, SocialUser $socialUser, ?Authenticatable $user = null)
    {
        $url = $this->getErrorRedirectUrl();

        return redirect()->to($url)->with('error', $this->getErrorMessage($error, $client, $socialUser, $user));
    }
}
