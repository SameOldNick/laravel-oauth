<?php

namespace SameOldNick\OAuth\Handlers\Pipeline\Steps;

use SameOldNick\OAuth\Concerns\CreatesConnectedAccountResponses;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackPipelineStep;
use SameOldNick\OAuth\Contracts\Services\OAuthGate;
use SameOldNick\OAuth\Enums\OAuthError;
use SameOldNick\OAuth\Handlers\Pipeline\OAuthCallbackPipelineContext;

class CheckCanLinkStep implements OAuthCallbackPipelineStep
{
    use CreatesConnectedAccountResponses;

    public function __construct(
        public readonly OAuthGate $gate,
    ) {
        //
    }

    public function __invoke(OAuthCallbackPipelineContext $context)
    {
        if (! $this->gate->canLink($context->getClient(), $context->getSocialUser(), $context->getCurrentUser() ?? $context->getUser())) {
            // Linking not allowed by gate
            return $this->createErrorResponse(OAuthError::CannotLink, $context->getClient(), $context->getSocialUser(), $context->getCurrentUser());
        }
    }
}
