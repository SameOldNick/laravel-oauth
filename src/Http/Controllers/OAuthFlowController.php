<?php

namespace SameOldNick\OAuth\Http\Controllers;

use SameOldNick\OAuth\Contracts\Handlers\OAuthFlowHandler;

class OAuthFlowController
{
    /**
     * Initializes controller.
     */
    public function __construct(
        protected readonly OAuthFlowHandler $flowHandler,
    ) {
        //
    }

    /**
     * Handles OAuth redirect
     */
    public function redirect(string $client)
    {
        return $this->flowHandler->handleOAuthRedirect($client);
    }

    /**
     * Handles OAuth callback
     */
    public function callback(string $client)
    {
        return $this->flowHandler->handleOAuthCallback($client);
    }
}
