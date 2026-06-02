<?php

namespace VendorName\OAuth\Fortify\Services;

use SameOldNick\OAuth\Concerns\CreatesConnectedAccountResponses;
use SameOldNick\OAuth\Concerns\Scaffolding\OAuthGate\AppliesOAuthGateRules;
use SameOldNick\OAuth\Contracts\Services\OAuthAuthenticationState;
use SameOldNick\OAuth\Contracts\Services\OAuthGate as OAuthGateContract;
use SameOldNick\OAuth\Contracts\Services\OAuthUserResolver;

class OAuthGate implements OAuthGateContract
{
    use AppliesOAuthGateRules;
    use CreatesConnectedAccountResponses;

    public function __construct(
        protected OAuthAuthenticationState $authenticationState,
        protected OAuthUserResolver $userResolver
    ) {
        //
    }
}
