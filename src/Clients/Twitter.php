<?php

namespace SameOldNick\OAuth\Clients;

use Laravel\Socialite\Contracts\User as SocialiteUser;

class Twitter extends Client
{
    /**
     * {@inheritDoc}
     */
    public function clientName(): string
    {
        return 'twitter';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return __('Twitter');
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRedirect(): void
    {
        $this->provider()->scopes($this->getScopes());
    }

    /**
     * {@inheritDoc}
     */
    public function defaultScopes(): array
    {
        return ['users.read'];
    }

    /**
     * {@inheritDoc}
     */
    public function getSocialUser(): SocialiteUser
    {
        $socialUser = parent::getSocialUser();

        // The email address maybe empty with X
        if (empty($socialUser->getEmail())) {
            // Instead, come up with e-mail address
            $socialUser->email = $this->generateEmail($socialUser);
        }

        return $socialUser;
    }

    /**
     * Generates email address to use when the email field is missing.
     */
    protected function generateEmail(SocialiteUser $socialiteUser): string
    {
        return sprintf('%s@x.com', $socialiteUser->getId());
    }
}
