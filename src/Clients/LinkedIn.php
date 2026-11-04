<?php

namespace SameOldNick\OAuth\Clients;

class LinkedIn extends Client
{
    /**
     * {@inheritDoc}
     */
    public function clientName(): string
    {
        return 'linkedin';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return __('LinkedIn');
    }
}
