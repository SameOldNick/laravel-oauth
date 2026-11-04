<?php

namespace SameOldNick\OAuth\Clients;

class Twitch extends Client
{
    /**
     * {@inheritDoc}
     */
    public function clientName(): string
    {
        return 'twitch';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return __('Twitch');
    }
}
