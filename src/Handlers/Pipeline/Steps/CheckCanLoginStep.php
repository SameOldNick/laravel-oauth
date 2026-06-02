<?php

namespace SameOldNick\OAuth\Handlers\Pipeline\Steps;

use SameOldNick\OAuth\Concerns\CreatesConnectedAccountResponses;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackPipelineStep;
use SameOldNick\OAuth\Contracts\Services\OAuthGate;
use SameOldNick\OAuth\Enums\OAuthError;
use SameOldNick\OAuth\Handlers\Pipeline\OAuthCallbackPipelineContext;

class CheckCanLoginStep implements OAuthCallbackPipelineStep
{
    use CreatesConnectedAccountResponses;

    public function __construct(
        public readonly OAuthGate $gate,
    ) {
        //
    }

    public function __invoke(OAuthCallbackPipelineContext $context)
    {
        /*
         * User is not logged in but the OAuth account is already linked to a user - log them in.
         */
        if (! $this->gate->canLogin($context->getClient(), $context->getSocialUser(), $context->getUser())) {
            // Login not allowed by gate
            return $this->createErrorResponse(OAuthError::LoginNotAllowed, $context->getClient(), $context->getSocialUser(), $context->getUser());
        }
    }
}
