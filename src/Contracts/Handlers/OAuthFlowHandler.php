<?php

namespace SameOldNick\OAuth\Contracts\Handlers;

interface OAuthFlowHandler
{
    /**
     * Handles OAuth redirect
     *
     * @return mixed
     */
    public function handleOAuthRedirect(string $client);

    /**
     * Handles OAuth callback
     *
     * @return mixed Response
     */
    public function handleOAuthCallback(string $client);
}
