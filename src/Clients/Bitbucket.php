<?php

namespace SameOldNick\OAuth\Clients;

class Bitbucket extends Client
{
    /**
     * {@inheritDoc}
     */
    public function clientName(): string
    {
        return 'bitbucket';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return __('Bitbucket');
    }
}
