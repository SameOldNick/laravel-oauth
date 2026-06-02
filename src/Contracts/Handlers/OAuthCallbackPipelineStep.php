<?php

namespace SameOldNick\OAuth\Contracts\Handlers;

use SameOldNick\OAuth\Handlers\Pipeline\OAuthCallbackPipelineContext;

interface OAuthCallbackPipelineStep
{
    public function __invoke(OAuthCallbackPipelineContext $context);
}
