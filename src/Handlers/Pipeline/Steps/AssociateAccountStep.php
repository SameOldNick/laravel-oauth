<?php

namespace SameOldNick\OAuth\Handlers\Pipeline\Steps;

use SameOldNick\OAuth\Contracts\Handlers\OAuthCallbackPipelineStep;
use SameOldNick\OAuth\Contracts\Services\OAuthAccountAssociator;
use SameOldNick\OAuth\Handlers\Pipeline\OAuthCallbackPipelineContext;

class AssociateAccountStep implements OAuthCallbackPipelineStep
{
    /**
     * Initializes handler dependencies.
     */
    public function __construct(
        public readonly OAuthAccountAssociator $accountAssociator,
    ) {
        //
    }

    public function __invoke(OAuthCallbackPipelineContext $context)
    {
        $this->accountAssociator->associate($context->getClient(), $context->getUser(), $context->getSocialUser());
    }
}
