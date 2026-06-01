<?php

namespace Workbench\App\OAuth\Fortify\Responses;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Fortify\Events\TwoFactorAuthenticationChallenged;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Contracts\Responses\AuthenticateResponse as AuthenticateResponseContract;

class AuthenticateResponse implements AuthenticateResponseContract
{
    public function __construct(
        protected readonly LoggedInResponse $loggedInResponse,
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function create(Client $client, SocialUser $socialUser, Authenticatable $user)
    {
        if (method_exists($user, 'hasEnabledTwoFactorAuthentication') && $user->hasEnabledTwoFactorAuthentication()) {
            session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => false,
            ]);

            TwoFactorAuthenticationChallenged::dispatch($user);

            return redirect()
                ->route('two-factor.login')
                ->with('status', __('oauth::messages.tfa_required', ['provider' => $client->getName()]));
        }

        // Log the user in and redirect to home
        $this->login($user);

        return $this->loggedInResponse->create($client, $socialUser, $user);
    }

    /**
     * Login user
     *
     * @return $this
     */
    protected function login(Authenticatable $user): static
    {
        Auth::login($user);

        return $this;
    }
}
