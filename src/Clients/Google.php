<?php

namespace SameOldNick\OAuth\Clients;

class Google extends Client
{
    /**
     * {@inheritDoc}
     */
    public function clientName(): string
    {
        return 'google';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return __('Google');
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRedirect(): void
    {
        $this->provider()->scopes(['profile', 'email']);
    }
}
