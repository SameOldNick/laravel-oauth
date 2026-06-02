<?php

namespace SameOldNick\OAuth\Handlers\Pipeline\Steps;

use SameOldNick\OAuth\Concerns\CreatesConnectedAccountResponses;
use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackPipelineStep;
use SameOldNick\OAuth\Handlers\Pipeline\OAuthCallbackPipelineContext;

class CreateAuthenticateResponseStep implements OAuthCallbackPipelineStep
{
    use CreatesConnectedAccountResponses;

    public function __invoke(OAuthCallbackPipelineContext $context)
    {
        return $this->createAuthenticateResponse($context->getClient(), $context->getSocialUser(), $context->getUser());
    }
}
