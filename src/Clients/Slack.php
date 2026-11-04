<?php

namespace SameOldNick\OAuth\Clients;

class Slack extends Client
{
    /**
     * {@inheritDoc}
     */
    public function clientName(): string
    {
        return 'slack';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return __('Slack');
    }
}
