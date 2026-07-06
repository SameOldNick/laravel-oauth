<?php

namespace SameOldNick\OAuth\Clients;

class Facebook extends Client
{
    /**
     * {@inheritDoc}
     */
    public function clientName(): string
    {
        return 'facebook';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return __('Facebook');
    }

    /**
     * {@inheritDoc}
     */
    public function prepareRedirect(): void
    {
        $this->provider()->scopes($this->getScopes());
    }
}
