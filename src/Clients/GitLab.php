<?php

namespace SameOldNick\OAuth\Clients;

class GitLab extends Client
{
    /**
     * {@inheritDoc}
     */
    public function clientName(): string
    {
        return 'gitlab';
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return __('GitLab');
    }
}
