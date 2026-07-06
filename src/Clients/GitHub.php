<?php

namespace SameOldNick\OAuth\Clients;

class GitHub extends Client
{
    /**
     * {@inheritDoc}
     */
    public function clientName(): string
    {
        return 'github';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return __('GitHub');
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
        return ['read:user'];
    }
}
