<?php

namespace VendorName\OAuth\Fortify\Services;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Socialite\Contracts\User as SocialUser;
use SameOldNick\OAuth\Clients\Client;
use SameOldNick\OAuth\Concerns\Scaffolding\HandlesOAuthUserRegistration;
use SameOldNick\OAuth\Contracts\Services\OAuthUserRegistrar as OAuthUserRegistrarContract;

class OAuthUserRegistrar implements OAuthUserRegistrarContract
{
    use HandlesOAuthUserRegistration;

    public function __construct(
        protected readonly CreatesNewUsers $userCreator,
    ) {
        //
    }

    /**
     * {@inheritDoc}
     */
    public function register(Client $client, SocialUser $socialUser): Authenticatable
    {
        // Re-use Laravel\Fortify's user creation logic to ensure things like events are properly handled.

        $password = $this->generateRandomPassword();

        $newUser = $this->userCreator->create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            // We need to provide a password, even if the user won't be using it, because Laravel\Fortify's default user creation logic requires it.
            // We'll generate a random password that meets the default password requirements.
            // The password will be set to null in the finalizeOAuthRegistration method, so the user won't be able to use it to log in without going through a password reset flow.
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        return $this->finalizeOAuthRegistration($client, $socialUser, $newUser);
    }

    /**
     * Generates a random password that meets the default Laravel\Fortify password requirements.
     * If it fails to generate a valid password after 10 attempts, it will return a random string of the minimum length.
     */
    protected function generateRandomPassword(): string
    {
        $rule = Password::default();
        $rules = $rule->appliedRules();

        $min = $rules['min'] ?? 8;
        $max = $rules['max'] ?? 64;

        // If all rules are false, we should include all character types to ensure we can generate a valid password.
        // Otherwise, the password salt will be empty and cause an error when generating the password.
        $default = ! $rules['letters'] && ! $rules['numbers'] && ! $rules['symbols'];

        for ($n = 1; $n <= 10; $n++) {
            try {
                $password = Str::password(
                    random_int($min, $max),
                    letters: $rules['letters'] ?: $default,
                    numbers: $rules['numbers'] ?: $default,
                    symbols: $rules['symbols'] ?: $default,
                );

                if ($rule->passes('password', $password)) {
                    return $password;
                }
            } catch (\Exception $e) {
                // If password doesn't meet the rules, try again (up to 10 times)
            }
        }

        // If we couldn't generate a valid password after 10 attempts, just return a random string of the minimum length.
        return Str::password(random_int($min, $max));
    }
}
