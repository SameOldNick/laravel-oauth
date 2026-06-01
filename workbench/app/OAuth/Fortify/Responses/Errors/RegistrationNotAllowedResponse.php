<?php

namespace Workbench\App\OAuth\Fortify\Responses\Errors;

use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Responses\Errors\RegistrationNotAllowedResponse as ErrorResponsesContract;

class RegistrationNotAllowedResponse implements ErrorResponsesContract
{
    public function create(Client $client, SocialUser $socialUser)
    {
        return redirect()
            ->route('login')
            ->with('error', __('oauth::messages.registration_not_allowed', ['provider' => $client->getName()]));
    }
}
